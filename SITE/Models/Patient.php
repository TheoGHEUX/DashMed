<?php

namespace Models;

use Core\Database;
use PDO;

/**
 * Gestion des patients et de leurs données
 *
 * Opérations de lecture pour les patients, leurs mesures, valeurs
 * et seuils d'alerte. Inclut des méthodes utilitaires pour la
 * normalisation des données destinées aux graphiques.
 *
 * @package Models
 */
final class Patient
{
    /**
     * Récupère les informations complètes d'un patient.
     *
     * Retourne toutes les données nécessaires pour l'affichage dans
     * le dashboard et le profil patient : identité, contact, groupe
     * sanguin, date de naissance.
     *
     * @param int $id Identifiant unique du patient (`pt_id`)
     * @return array|null Tableau associatif du patient :
     *                    - 'pt_id' (int) Identifiant du patient
     *                    - 'prenom' (string) Prénom
     *                    - 'nom' (string) Nom
     *                    - 'email' (string) Adresse e-mail
     *                    - 'sexe' (string) 'M' ou 'F'
     *                    - 'groupe_sanguin' (string)
     *                    - 'date_naissance' (string) Format YYYY-MM-DD
     *                    - 'telephone' (string)
     *                    - 'ville' (string)
     *                    - 'code_postal' (string)
     *                    - 'adresse' (string)
     *                    Retourne null si aucun patient trouvé.
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
        $patient = $st->fetch(PDO::FETCH_ASSOC);
        return $patient ?: null;
    }

    /**
     * Récupère la liste des patients suivis par un médecin.
     *
     * Retourne un tableau contenant uniquement les informations nécessaires
     * pour lister les patients : identifiant, nom et prénom.
     *
     * Note : Identifiant essentiel pour créer les liens vers le dashboard
     *
     * @param int $doctorId Identifiant du médecin
     * @return array Liste des patients. Chaque élément contient :
     *               - 'pt_id' (int) Identifiant unique
     *               - 'nom' (string)
     *               - 'prenom' (string)
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
        WHERE s.med_id = :med_id
        ORDER BY p.nom, p.prenom
    ");

        $stmt->execute([':med_id' => $doctorId]);

        return $stmt->fetchAll();
    }

    /**
     * Récupère toutes les mesures associées à un patient.
     *
     * Chaque mesure représente un type de donnée médicale collectée
     * (fréquence cardiaque, tension artérielle, température, etc.).
     *
     * @param int $patientId  Identifiant du patient
     * @param int $medId      Identifiant du médecin
     * @return array|null     Données du patient ou null si non autorisé
     */
    public static function findByIdForDoctor(int $patientId, int $medId): ?array
    {
        $pdo = Database::getConnection();
        $st = $pdo->prepare('
            SELECT 
                p.pt_id,
                p.prenom,
                p.nom,
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
     * Récupère les données pour un graphique avec vérification d'autorisation.
     *
     * Processus :
     * 1. Vérifie que le médecin suit bien ce patient
     * 2. Récupère l'id_mesure correspondant au type demandé
     * 3. Récupère les N dernières valeurs
     * 4. Inverse l'ordre pour un affichage chronologique
     *
     * @param int    $medId      Identifiant du médecin
     * @param int    $patientId  Identifiant du patient
     * @param string $typeMesure Type de mesure (ex: "Température")
     * @param int    $limit      Nombre de points à récupérer
     * @return array|null        Données du graphique ou null si non autorisé
     */
    public static function getChartDataForDoctor(
        int $medId,
        int $patientId,
        string $typeMesure,
        int $limit = 50
    ): ?array {
        $pdo = Database::getConnection();

        // Vérifier que le médecin suit ce patient
        $check = $pdo->prepare('
            SELECT 1 FROM suivre WHERE med_id = ? AND pt_id = ? LIMIT 1
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
        $mesure = $st->fetch(PDO::FETCH_ASSOC);

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
     * Chaque mesure représente un type de donnée médicale collectée
     * (fréquence cardiaque, tension artérielle, température, etc.).
     *
     * @param int $patientId Identifiant du patient
     * @return array Liste de mesures contenant :
     *               - 'id_mesure' (int) Identifiant unique
     *               - 'type_mesure' (string) Ex: 'Fréquence cardiaque'
     *               - 'unite' (string) Ex: 'BPM', 'mmHg', '°C'
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
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère les valeurs d'une mesure spécifique.
     *
     * Retourne l'historique des valeurs pour une mesure donnée,
     * triées de la plus récente à la plus ancienne.
     *
     * @param int      $mesureId Identifiant de la mesure
     * @param int|null $limit    Nombre max de valeurs (optionnel)
     * @return array<int,array> Liste des valeurs contenant :
     *                          id_val, valeur, date_mesure,
     *                          heure_mesure, datetime_mesure
     */
    public static function getValeursMesure(int $mesureId, ?int $limit = null): array
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
     * Récupère les dernières valeurs pour chaque mesure d'un patient.
     *
     * Retourne un snapshot des dernières valeurs de toutes les mesures.
     *
     * Utilisé pour l'affichage du résumé rapide dans le dashboard.
     *
     * @param int $patientId Identifiant du patient
     * @return array Liste des mesures avec dernière valeur et date/heure
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
                vm.heure_mesure as derniere_heure
            FROM mesures m
            INNER JOIN valeurs_mesures vm ON m.id_mesure = vm.id_mesure
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
     * Récupère les données pour un graphique.
     *
     * Retourne les N dernières valeurs d'un type de mesure spécifique.
     *
     * Note : Les valeurs sont inversées pour un ordre chronologique.
     *
     * @param int    $patientId  Identifiant du patient
     * @param string $typeMesure Type de mesure (ex: "poids")
     * @param int    $limit      Nombre de points (défaut 50)
     * @return array|null        Tableau avec type_mesure, unite, valeurs
     */
    public static function getChartData(
        int $patientId,
        string $typeMesure,
        int $limit = 50
    ): ?array {
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
        $valeurs = $st->fetchAll(PDO::FETCH_ASSOC);

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
     * Utilisé pour la mise à l'échelle des valeurs dans les graphiques.
     *
     * Retourne 0.5 si min == max (évite division par zéro).
     *
     * @param float $value Valeur à normaliser
     * @param float $min   Valeur minimale attendue
     * @param float $max   Valeur maximale attendue
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
     * Prépare les données normalisées pour les graphiques JavaScript.
     *
     * Applique la normalisation à un tableau de valeurs issues de la base.
     *
     * Chaque élément du tableau doit contenir une clé 'valeur'.
     *
     * @param array $valeurs Liste de valeurs (clé 'valeur' requise)
     * @param float $min     Min pour la normalisation
     * @param float $max     Max pour la normalisation
     * @return array         Valeurs normalisées entre 0 et 1
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
     * Utilisé comme valeur par défaut lorsque aucun patient n'est
     * sélectionné dans le dashboard.
     *
     * @param int $medId Identifiant du médecin
     * @return int|null  Identifiant du premier patient, ou null
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

        return $row ? (int) $row['pt_id'] : null;
    }

    /**
     * Récupère la valeur seuil pour un patient et une mesure.
     *
     * Les seuils d'alerte définissent les limites pour les statuts
     * 'préoccupant', 'urgent' ou 'critique'.
     *
     * Chaque seuil peut être majorant (max) ou minorant (min).
     *
     * @param int    $patientId  Identifiant du patient
     * @param string $typeMesure Type de mesure
     * @param string $statut     'préoccupant', 'urgent' ou 'critique'
     * @param bool   $majorant   True = seuil max, False = seuil min
     * @return float|null        Valeur du seuil ou null si non défini
     */
    public static function getSeuilByStatus(
        int $patientId,
        string $typeMesure,
        string $statut,
        bool $majorant
    ): ?float {
        $pdo = Database::getConnection();

        $sql = "
        SELECT seuil
        FROM seuil_alerte sa
        JOIN mesures m ON m.id_mesure = sa.id_mesure
        WHERE sa.statut = :statut
          AND m.type_mesure = :type
          AND m.pt_id = :pt_id
          AND sa.majorant = :majorant
        LIMIT 1
    ";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':statut' => $statut,
            ':type' => $typeMesure,
            ':pt_id' => $patientId,
            ':majorant' => $majorant ? 1 : 0
        ]);

        $row = $stmt->fetch();
        return $row ? (float) $row['seuil'] : null;
    }
}
