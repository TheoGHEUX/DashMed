<?php

namespace Models;

use Core\Database;
use PDO;

/**
 * Class Patient
 *
 * Fournit les opérations de lecture et préparation des données patients.
 * Méthodes statiques pour récupérer patient, mesures, valeurs et données
 * destinées aux graphiques.
 *
 * @package Models
 */
final class Patient
{
    /**
     * Récupère un patient par son ID.
     *
     * @param int $id Identifiant du patient (pt_id)
     * @return array|null Tableau associatif du patient ou null si non trouvé
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
     * Récupère un patient appartenant au médecin donné (jointure suivre).
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
            INNER JOIN suivre s ON s.pt_id = p.pt_id AND s.med_id = ?
            WHERE p.pt_id = ?
            LIMIT 1
        ');
        $st->execute([$medId, $patientId]);
        $patient = $st->fetch(PDO::FETCH_ASSOC);
        return $patient ?: null;
    }

    /**
     * Récupère tous les patients suivis par un médecin.
     *
     * @param int $medId Identifiant du médecin
     * @return array Liste des patients avec leurs informations de base
     */
    public static function getPatientsForDoctor(int $medId): array
    {
        $pdo = Database::getConnection();
        $st = $pdo->prepare('
            SELECT 
                p.pt_id,
                p.prenom,
                p.nom,
                p.email,
                p.sexe,
                p.date_naissance
            FROM patient p
            INNER JOIN suivre s ON p.pt_id = s.pt_id
            WHERE s.med_id = ?
            ORDER BY p.nom, p.prenom
        ');
        $st->execute([$medId]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère toutes les mesures d'un patient.
     *
     * @param int $patientId Identifiant du patient (pt_id)
     * @return array Liste des mesures (id_mesure, type_mesure, unite)
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
     * @param int      $mesureId Identifiant de la mesure
     * @param int|null $limit    Nombre maximum de valeurs à retourner (optionnel)
     * @return array Liste des valeurs (id_val, valeur, date_mesure, heure_mesure, datetime_mesure)
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
     * Récupère les dernières valeurs pour chaque type de mesure d'un patient.
     *
     * @param int $patientId Identifiant du patient
     * @return array Liste des mesures avec leur dernière valeur et date/heure
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
     * Récupère les données pour un graphique (dernières N valeurs) pour un type de mesure.
     *
     * @param int    $patientId  Identifiant du patient
     * @param string $typeMesure Type de mesure (ex: "poids", "tension")
     * @param int    $limit      Nombre de points à récupérer (par défaut 50)
     * @return array|null Tableau contenant type_mesure, unite et valeurs (ou null si pas de mesure)
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
        $valeurs = $st->fetchAll(PDO::FETCH_ASSOC);

        // Inverser pour avoir les plus anciennes en premier (ordre chronologique)
        $valeurs = array_reverse($valeurs);

        return [
            'type_mesure' => $typeMesure,
            'unite' => $mesure['unite'],
            'valeurs' => $valeurs
        ];
    }

    /**
     * Récupère les données pour un graphique en validant l'appartenance patient/médecin.
     */
    public static function getChartDataForDoctor(
        int $medId,
        int $patientId,
        string $typeMesure,
        int $limit = 50
    ): ?array {
        $pdo = Database::getConnection();

        // Récupérer l'id_mesure pour ce type en vérifiant le lien au médecin
        $st = $pdo->prepare('
            SELECT m.id_mesure, m.unite
            FROM mesures m
            INNER JOIN suivre s ON s.pt_id = m.pt_id AND s.med_id = ?
            WHERE m.pt_id = ? AND m.type_mesure = ?
            LIMIT 1
        ');
        $st->execute([$medId, $patientId, $typeMesure]);
        $mesure = $st->fetch(PDO::FETCH_ASSOC);

        if (!$mesure) {
            return null;
        }

        $st = $pdo->prepare('
            SELECT 
                vm.valeur,
                vm.date_mesure,
                vm.heure_mesure
            FROM valeurs_mesures vm
            INNER JOIN mesures m ON m.id_mesure = vm.id_mesure
            INNER JOIN suivre s ON s.pt_id = m.pt_id AND s.med_id = ?
            WHERE vm.id_mesure = ?
            ORDER BY vm.date_mesure DESC, vm.heure_mesure DESC
            LIMIT ?
        ');
        $st->bindValue(1, $medId, PDO::PARAM_INT);
        $st->bindValue(2, $mesure['id_mesure'], PDO::PARAM_INT);
        $st->bindValue(3, $limit, PDO::PARAM_INT);
        $st->execute();
        $valeurs = $st->fetchAll(PDO::FETCH_ASSOC);
        $valeurs = array_reverse($valeurs);

        return [
            'type_mesure' => $typeMesure,
            'unite' => $mesure['unite'],
            'valeurs' => $valeurs
        ];
    }

    /**
     * Normalise une valeur entre 0 et 1 selon un min/max.
     *
     * @param float $value Valeur à normaliser
     * @param float $min   Valeur minimale attendue
     * @param float $max   Valeur maximale attendue
     * @return float Valeur normalisée entre 0 et 1 (0.5 si min==max)
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
     * @param array $valeurs Liste de valeurs issues de la base (chaque élément doit avoir la clé 'valeur')
     * @param float $min     Min pour la normalisation
     * @param float $max     Max pour la normalisation
     * @return array Tableau des valeurs normalisées (float)
     */
    public static function prepareChartValues(array $valeurs, float $min, float $max): array
    {
        return array_map(function ($v) use ($min, $max) {
            return self::normalizeValue((float)$v['valeur'], $min, $max);
        }, $valeurs);
    }

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
}
