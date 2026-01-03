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
     * Récupère les informations complètes d'un patient à partir de son identifiant.
     *
     * Cette méthode retourne un tableau associatif contenant toutes les données
     * pertinentes du patient pour l'affichage dans le dashboard ou d'autres interfaces :
     * prénom, nom, email, sexe, groupe sanguin, date de naissance, téléphone, adresse et ville.
     *
     * @param int $id Identifiant unique du patient (`pt_id`).
     *
     * @return array|null Tableau associatif du patient avec les clés suivantes :
     *                     - 'pt_id' => int Identifiant du patient
     *                     - 'prenom' => string Prénom du patient
     *                     - 'nom' => string Nom du patient
     *                     - 'email' => string Adresse e-mail
     *                     - 'sexe' => string Sexe du patient ('M' ou 'F')
     *                     - 'groupe_sanguin' => string Groupe sanguin
     *                     - 'date_naissance' => string Date de naissance au format YYYY-MM-DD
     *                     - 'telephone' => string Numéro de téléphone
     *                     - 'ville' => string Ville de résidence
     *                     - 'code_postal' => string Code postal
     *                     - 'adresse' => string Adresse complète
     *                   Retourne `null` si aucun patient correspondant n'est trouvé.
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
     * Cette méthode retourne un tableau associatif contenant uniquement les informations
     * nécessaires pour lister les patients : l'identifiant ('pt_id'), le nom et le prénom.
     * L'identifiant du patient est essentiel pour créer les liens vers le tableau de bord
     * ou d'autres actions côté frontend, même si toutes les données ne sont pas affichées.
     *
     * @param int $doctorId L'identifiant du médecin dont on souhaite récupérer les patients.
     *
     * @return array Un tableau de patients. Chaque élément est un tableau associatif avec les clés :
     *               - 'pt_id' => int Identifiant unique du patient
     *               - 'nom' => string Nom du patient
     *               - 'prenom' => string Prénom du patient
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
     * Récupère toutes les mesures associées à un patient donné.
     *
     * Chaque mesure représente un type de donnée médicale collectée pour le patient,
     * comme la fréquence cardiaque, la tension artérielle, la température, etc.
     *
     * @param int $patientId Identifiant unique du patient (`pt_id`).
     *
     * @return array Liste de mesures, chaque élément étant un tableau associatif contenant :
     *               - 'id_mesure' => int Identifiant unique de la mesure
     *               - 'type_mesure' => string Type de mesure (ex. 'Fréquence cardiaque')
     *               - 'unite' => string Unité de la mesure (ex. 'BPM', 'mmHg', '°C')
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

    /**
     * Récupère l'identifiant du premier patient associé à un médecin donné.
     *
     * Cette méthode retourne le pt_id du patient suivi par le médecin avec l'ID
     * '$medId', selon l'ordre croissant des identifiants. Utile comme valeur
     * par défaut lorsque aucun patient n'est sélectionné.
     *
     * @param int $medId L'identifiant du médecin (med_id)
     * @return int|null L'identifiant du premier patient suivi par le médecin,
     *                  ou null si aucun patient n'est associé
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

    public static function getSeuilPreoccupant(
        int $patientId,
        string $typeMesure
    ): ?float {
        $pdo = Database::getConnection();

        $sql = "
        SELECT sa.seuil_max
        FROM seuil_alerte sa
        JOIN mesures m ON m.id_mesure = sa.id_mesure
        WHERE sa.statut = 'préoccupant'
          AND m.type_mesure = :type
          AND m.pt_id = :pt_id
        LIMIT 1
    ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':type'   => $typeMesure,
            ':pt_id' => $patientId
        ]);

        $row = $stmt->fetch();

        return $row ? (float) $row['seuil_max'] : null;
    }

    public static function getSeuilUrgent(
        int $patientId,
        string $typeMesure
    ): ?float {
        $pdo = Database::getConnection();

        $sql = "
        SELECT sa.seuil_max
        FROM seuil_alerte sa
        JOIN mesures m ON m.id_mesure = sa.id_mesure
        WHERE sa.statut = 'urgent'
          AND m.type_mesure = :type
          AND m.pt_id = :pt_id
        LIMIT 1
    ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':type'   => $typeMesure,
            ':pt_id' => $patientId
        ]);

        $row = $stmt->fetch();

        return $row ? (float) $row['seuil_max'] : null;
    }

    public static function getSeuilCritique(
        int $patientId,
        string $typeMesure
    ): ?float {
        $pdo = Database::getConnection();

        $sql = "
        SELECT sa.seuil_max
        FROM seuil_alerte sa
        JOIN mesures m ON m.id_mesure = sa.id_mesure
        WHERE sa.statut = 'critique'
          AND m.type_mesure = :type
          AND m.pt_id = :pt_id
        LIMIT 1
    ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':type'   => $typeMesure,
            ':pt_id' => $patientId
        ]);

        $row = $stmt->fetch();

        return $row ? (float) $row['seuil_max'] : null;
    }
}
