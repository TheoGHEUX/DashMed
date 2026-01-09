<?php

namespace Models;

use Core\Database;
use PDO;

/**
 * Modèle de gestion des patients et de leurs données médicales.
 *
 * Fournit des méthodes pour récupérer les informations des patients, leurs mesures,
 * valeurs et données formatées pour les graphiques. Gère aussi les autorisations
 * d'accès et ce basées sur la relation médecin-patient.
 *
 * @package Models
 */
final class Patient
{
    /**
     * Récupère les informations complètes d'un patient par son identifiant.
     *
     * Retourne toutes les données personnelles et médicales du patient nécessaires
     * pour l'affichage dans le dashboard ou les interfaces de consultation.
     *
     * @param int $id Identifiant unique du patient
     * @return array|null Tableau associatif contenant pt_id, prenom, nom, email, sexe,
     *                    groupe_sanguin, date_naissance, telephone, ville, code_postal, adresse.
     *                    Null si le patient n'existe pas.
     */
    public static function findById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $st = $pdo->prepare('
            SELECT 
                pt_id,
                prenom,
                nom,
                email,
                sexe,
                groupe_sanguin,
                date_naissance,
                telephone,
                ville,
                code_postal,
                adresse
            FROM patient
            WHERE pt_id = ? 
            LIMIT 1
        ');
        $st->execute([$id]);
        $patient = $st->fetch(PDO:: FETCH_ASSOC);
        return $patient ?: null;
    }

    /**
     * Récupère la liste des patients suivis par un médecin.
     *
     * Retourne uniquement les informations nécessaires pour l'affichage en liste
     * (identifiant, nom, prénom), triées par nom puis prénom.
     *
     * @param int $doctorId Identifiant du médecin
     * @return array<int,array<string,mixed>> Tableau de patients avec pt_id, nom, prenom
     */
    public static function getPatientsForDoctor(int $doctorId): array
    {
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("
        SELECT 
            p.pt_id,
            p.nom,
            p.prenom
        FROM suivre s
        JOIN patient p ON p.pt_id = s.pt_id
        WHERE s.med_id = : med_id
        ORDER BY p. nom, p.prenom
    ");

        $stmt->execute([':med_id' => $doctorId]);

        return $stmt->fetchAll();
    }

    /**
     * Récupère un patient en vérifiant l'autorisation d'accès du médecin.
     *
     * Assure que le médecin est autorisé à accéder aux données du patient en
     * vérifiant l'existence de la relation dans la table 'suivre'.  Retourne null
     * si le médecin ne suit pas ce patient.
     *
     * @param int $patientId Identifiant du patient
     * @param int $medId Identifiant du médecin
     * @return array|null Données complètes du patient ou null si accès non autorisé
     */
    public static function findByIdForDoctor(int $patientId, int $medId): ?array
    {
        $pdo = Database::getConnection();
        $st = $pdo->prepare('
            SELECT 
                p.pt_id,
                p.prenom,
                p. nom,
                p.email,
                p.sexe,
                p.groupe_sanguin,
                p.date_naissance,
                p.telephone,
                p.ville,
                p.code_postal,
                p.adresse
            FROM patient p
            JOIN suivre s ON s.pt_id = p.pt_id
            WHERE p.pt_id = ? AND s.med_id = ?
            LIMIT 1
        ');
        $st->execute([$patientId, $medId]);
        $patient = $st->fetch(PDO::FETCH_ASSOC);
        return $patient ?: null;
    }

    /**
     * Récupère les données d'un graphique avec vérification d'autorisation.
     *
     * Vérifie que le médecin suit le patient avant de retourner les données.
     * Les valeurs sont inversées pour obtenir un ordre chronologique (anciennes en premier).
     *
     * @param int $medId Identifiant du médecin
     * @param int $patientId Identifiant du patient
     * @param string $typeMesure Type de mesure (ex: "Température corporelle", "Fréquence cardiaque")
     * @param int $limit Nombre de points de données à récupérer (défaut :  50)
     * @return array|null Tableau contenant type_mesure, unite, valeurs ou null si non autorisé/introuvable
     */
    public static function getChartDataForDoctor(int $medId, int $patientId, string $typeMesure, int $limit = 50): ?array
    {
        $pdo = Database:: getConnection();

        // Vérifier que le médecin suit ce patient
        $check = $pdo->prepare('
            SELECT 1 FROM suivre WHERE med_id = ? AND pt_id = ?  LIMIT 1
        ');
        $check->execute([$medId, $patientId]);
        if (!$check->fetch()) {
            return null; // Non autorisé
        }

        // Récupérer l'id_mesure pour ce type
        $st = $pdo->prepare('
            SELECT id_mesure, unite
            FROM mesures
            WHERE pt_id = ? AND type_mesure = ?
            LIMIT 1
        ');
        $st->execute([$patientId, $typeMesure]);
        $mesure = $st->fetch(PDO:: FETCH_ASSOC);

        if (!$mesure) {
            return null;
        }

        // Récupérer les valeurs
        $st = $pdo->prepare('
            SELECT 
                valeur,
                date_mesure,
                heure_mesure
            FROM valeurs_mesures
            WHERE id_mesure = ?
            ORDER BY date_mesure DESC, heure_mesure DESC
            LIMIT ? 
        ');
        $st->execute([$mesure['id_mesure'], $limit]);
        $valeurs = $st->fetchAll(PDO::FETCH_ASSOC);

        // Inverser pour avoir les plus anciennes en premier
        $valeurs = array_reverse($valeurs);

        return [
            'type_mesure' => $typeMesure,
            'unite' => $mesure['unite'],
            'valeurs' => $valeurs
        ];
    }

    /**
     * Récupère toutes les mesures associées à un patient.
     *
     * Chaque mesure représente un type de donnée médicale collectée (fréquence cardiaque,
     * tension artérielle, température, etc.).
     *
     * @param int $patientId Identifiant du patient
     * @return array<int,array<string,mixed>> Liste des mesures avec id_mesure, type_mesure, unite
     */
    public static function getMesures(int $patientId): array
    {
        $pdo = Database::getConnection();
        $st = $pdo->prepare('
            SELECT 
                id_mesure,
                type_mesure,
                unite
            FROM mesures
            WHERE pt_id = ?
            ORDER BY id_mesure
        ');
        $st->execute([$patientId]);
        return $st->fetchAll(PDO:: FETCH_ASSOC);
    }

    /**
     * Récupère les valeurs d'une mesure spécifique.
     *
     * Les valeurs sont triées par date et heure décroissantes (plus récentes en premier).
     * Inclut un champ calculé 'datetime_mesure' pour faciliter l'affichage.
     *
     * @param int $mesureId Identifiant de la mesure
     * @param int|null $limit Nombre maximum de valeurs à retourner (optionnel)
     * @return array<int,array<string,mixed>> Liste des valeurs avec id_val, valeur, date_mesure,
     *                                         heure_mesure, datetime_mesure
     */
    public static function getValeursMesure(int $mesureId, ? int $limit = null): array
    {
        $pdo = Database::getConnection();
        $sql = '
            SELECT 
                id_val,
                valeur,
                date_mesure,
                heure_mesure,
                CONCAT(date_mesure, " ", heure_mesure) as datetime_mesure
            FROM valeurs_mesures
            WHERE id_mesure = ?
            ORDER BY date_mesure DESC, heure_mesure DESC
        ';

        if ($limit !== null) {
            $sql .= ' LIMIT ' . (int)$limit;
        }

        $st = $pdo->prepare($sql);
        $st->execute([$mesureId]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère la dernière valeur enregistrée pour chaque type de mesure d'un patient.
     *
     * Plutot utile pour afficher un récapitulatif des données les plus récentes du patient.
     *
     * @param int $patientId Identifiant du patient
     * @return array<int,array<string,mixed>> Liste des mesures avec id_mesure, type_mesure, unite,
     *                                         derniere_valeur, derniere_date, derniere_heure
     */
    public static function getDernieresValeurs(int $patientId): array
    {
        $pdo = Database::getConnection();
        $st = $pdo->prepare('
            SELECT 
                m.id_mesure,
                m.type_mesure,
                m.unite,
                vm.valeur as derniere_valeur,
                vm.date_mesure as derniere_date,
                vm. heure_mesure as derniere_heure
            FROM mesures m
            INNER JOIN valeurs_mesures vm ON m.id_mesure = vm. id_mesure
            WHERE m.pt_id = ?
            AND (vm.date_mesure, vm.heure_mesure) = (
                SELECT date_mesure, heure_mesure
                FROM valeurs_mesures
                WHERE id_mesure = m.id_mesure
                ORDER BY date_mesure DESC, heure_mesure DESC
                LIMIT 1
            )
            ORDER BY m.type_mesure
        ');
        $st->execute([$patientId]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère les données formatées pour un graphique sur un type de mesure.
     *
     * Les valeurs sont retournées en ordre chronologique (anciennes en premier)
     * pour faciliter l'affichage sur les graphiques.
     *
     * @param int $patientId Identifiant du patient
     * @param string $typeMesure Type de mesure (ex: "poids", "tension")
     * @param int $limit Nombre de points à récupérer (défaut : 50)
     * @return array|null Tableau contenant id_mesure, type_mesure, unite, valeurs
     *                    ou null si la mesure n'existe pas
     */
    public static function getChartData(int $patientId, string $typeMesure, int $limit = 50): ?array
    {
        $pdo = Database::getConnection();

        // Récupérer l'id_mesure pour ce type
        $st = $pdo->prepare('
            SELECT id_mesure, unite
            FROM mesures
            WHERE pt_id = ? AND type_mesure = ? 
            LIMIT 1
        ');
        $st->execute([$patientId, $typeMesure]);
        $mesure = $st->fetch(PDO::FETCH_ASSOC);

        if (!$mesure) {
            return null;
        }

        // Récupérer les valeurs (les plus récentes en premier, puis on inverse)
        $st = $pdo->prepare('
            SELECT 
                valeur,
                date_mesure,
                heure_mesure
            FROM valeurs_mesures
            WHERE id_mesure = ?
            ORDER BY date_mesure DESC, heure_mesure DESC
            LIMIT ?
        ');
        $st->execute([$mesure['id_mesure'], $limit]);
        $valeurs = $st->fetchAll(PDO:: FETCH_ASSOC);

        // Inverser pour avoir les plus anciennes en premier (ordre chronologique)
        $valeurs = array_reverse($valeurs);

        return [
            'id_mesure' => $mesure['id_mesure'],
            'type_mesure' => $typeMesure,
            'unite' => $mesure['unite'],
            'valeurs' => $valeurs
        ];
    }

    /**
     * Normalise une valeur entre 0 et 1 selon un intervalle min/max.
     *
     * Utilisé pour préparer les données des graphiques avec des échelles cohérentes.
     * Retourne 0.5 si min et max sont égaux (évite la division par zéro).
     *
     * @param float $value Valeur à normaliser
     * @param float $min Valeur minimale de l'intervalle
     * @param float $max Valeur maximale de l'intervalle
     * @return float Valeur normalisée entre 0 et 1
     */
    public static function normalizeValue(float $value, float $min, float $max): float
    {
        if ($max === $min) {
            return 0.5;
        }
        return max(0, min(1, ($value - $min) / ($max - $min)));
    }

    /**
     * Prépare un tableau de valeurs normalisées pour les graphiques JavaScript.
     *
     * Applique la normalisation à chaque valeur d'un tableau selon l'intervalle fourni.
     *
     * @param array<int,array<string,mixed>> $valeurs Liste de valeurs avec clé 'valeur'
     * @param float $min Valeur minimale pour la normalisation
     * @param float $max Valeur maximale pour la normalisation
     * @return array<int,float> Tableau des valeurs normalisées
     */
    public static function prepareChartValues(array $valeurs, float $min, float $max): array
    {
        return array_map(function ($v) use ($min, $max) {
            return self::normalizeValue((float)$v['valeur'], $min, $max);
        }, $valeurs);
    }

    /**
     * Récupère l'identifiant du premier patient suivi par un médecin.
     *
     * Utile pour définir un patient par défaut lorsqu'aucun n'est sélectionné
     * explicitement. Trie les patients par identifiant croissant.
     *
     * @param int $medId Identifiant du médecin
     * @return int|null Identifiant du premier patient ou null si aucun patient suivi
     */
    public static function getFirstPatientIdForDoctor(int $medId): ?int
    {
        $pdo = Database::getConnection();

        $st = $pdo->prepare('
        SELECT pt_id
        FROM suivre
        WHERE med_id = ?
        ORDER BY pt_id
        LIMIT 1
    ');

        $st->execute([$medId]);
        $row = $st->fetch(PDO::FETCH_ASSOC);

        return $row ? (int) $row['pt_id'] :  null;
    }

    /**
     * Récupère un seuil d'alerte pour une mesure donnée selon le statut.
     *
     * Les seuils permettent de déterminer si une valeur est préoccupante, urgente ou critique.
     * Le paramètre $majorant détermine s'il s'agit d'un seuil maximum (true) ou minimum (false).
     *
     * @param int $patientId Identifiant du patient
     * @param string $typeMesure Type de mesure concerné
     * @param string $statut Statut du seuil ('préoccupant', 'urgent', 'critique')
     * @param bool $majorant True pour seuil maximum, false pour seuil minimum
     * @return float|null Valeur du seuil ou null si non défini
     */
    public static function getSeuilByStatus(int $patientId, string $typeMesure, string $statut, bool $majorant): ?float
    {
        $pdo = Database::getConnection();

        $sql = "
        SELECT seuil
        FROM seuil_alerte sa
        JOIN mesures m ON m.id_mesure = sa. id_mesure
        WHERE sa.statut = : statut
          AND m.type_mesure = :type
          AND m.pt_id = :pt_id
          AND sa.majorant = :majorant
        LIMIT 1
    ";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':statut' => $statut,
            ':type' => $typeMesure,
            ': pt_id' => $patientId,
            ':majorant' => $majorant ?  1 : 0
        ]);

        $row = $stmt->fetch();
        return $row ? (float) $row['seuil'] :  null;
    }
}