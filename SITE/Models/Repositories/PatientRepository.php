<?php

namespace Models\Repositories;

use Core\Database;
use Models\Entities\Patient;
use PDO;

/**
 * Dépôt Patient
 *
 * Gère les interactions avec la base de données concernant les patients
 *
 * S'occupe de récupérer les profils, les listes de patients par médecin,
 * mais aussi les mesures pour les graphiques et
 * la personnalisation du tableau de bord.
 *
 * @package Models\Repositories
 */
class PatientRepository
{
    private PDO $db;

    /**
     * Constructeur : Initialise la connexion à la base de données
     */
    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Trouve un patient grâce à son unique identifiant
     *
     * @param int $id L'ID du patient
     * @return Patient|null L'objet Patient ou null si introuvable
     */
    public function findById(int $id): ?Patient
    {
        $stmt = $this->db->prepare('
            SELECT * FROM patient WHERE pt_id = ? LIMIT 1
        ');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? new Patient($row) : null;
    }

    /**
     * Récupère la liste de tous les patients suivis par un médecin
     *
     * @param int $medId L'ID du médecin connecté
     * @return Patient[] Tableau d'objets Patient triés par nom
     */
    public function getPatientsForDoctor(int $medId): array
    {
        $stmt = $this->db->prepare("
            SELECT p.* 
            FROM suivre s
            JOIN patient p ON p.pt_id = s.pt_id
            WHERE s.med_id = ?
            ORDER BY p.nom, p.prenom
        ");
        $stmt->execute([$medId]);

        $patients = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $patients[] = new Patient($row); // On transforme les lignes SQL en objets
        }
        return $patients;
    }

    /**
     * Récupère les données pour construire un graphique
     *
     * Cherche les mesures d'un type précis (ex: "Tension") pour un patient,
     * et renvoie les dernières valeurs enregistrées
     *
     * @param int    $patientId   ID du patient
     * @param string $typeMesure  Nom de la mesure
     * @param int    $limit       Nombre de points à récupérer (défaut 50)
     *
     * @return array|null Un tableau structuré pour le graphique ou null si pas de données
     */
    public function getChartData(int $patientId, string $typeMesure, int $limit = 50): ?array
    {
        // 1. On trouve l'ID et l'unité de la mesure
        $stmt = $this->db->prepare('
            SELECT id_mesure, unite FROM mesures 
            WHERE pt_id = ? AND type_mesure = ? LIMIT 1
        ');
        $stmt->execute([$patientId, $typeMesure]);
        $mesure = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$mesure) return null;

        // 2. On récupère les valeurs brutes
        $stmt = $this->db->prepare('
            SELECT valeur, date_mesure, heure_mesure 
            FROM valeurs_mesures 
            WHERE id_mesure = ? 
            ORDER BY date_mesure DESC, heure_mesure DESC 
            LIMIT ?
        ');
        $stmt->execute([$mesure['id_mesure'], $limit]);
        $valeurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 3. On retourne le tout (en inversant pour avoir l'ordre chronologique)
        return [
            'id_mesure' => $mesure['id_mesure'],
            'type_mesure' => $typeMesure,
            'unite' => $mesure['unite'],
            'valeurs' => array_reverse($valeurs)
        ];
    }

    /**
     * Récupère un seuil d'alerte spécifique
     *
     *
     * @param int    $patientId   ID du patient
     * @param string $typeMesure  Type de mesure
     * @param string $statut      Niveau d'alerte ('critique', 'urgent', etc.)
     * @param bool   $majorant    Vrai pour le seuil max, Faux pour le seuil min
     *
     * @return float|null La valeur du seuil ou null s'il n'existe pas
     */
    public function getSeuilByStatus(int $patientId, string $typeMesure, string $statut, bool $majorant): ?float
    {
        $stmt = $this->db->prepare("
            SELECT seuil FROM seuil_alerte sa
            JOIN mesures m ON m.id_mesure = sa.id_mesure
            WHERE sa.statut = ? 
            AND m.type_mesure = ? 
            AND m.pt_id = ? 
            AND sa.majorant = ?
            LIMIT 1
        ");
        $stmt->execute([$statut, $typeMesure, $patientId, $majorant ? 1 : 0]);
        $row = $stmt->fetch();
        return $row ? (float)$row['seuil'] : null;
    }

    /**
     * Normalise des valeurs pour l'affichage (entre 0 et 1).
     *
     * Utile pour certains types de visualisations ou calculs de tendances
     *
     * @param array $valeurs Liste des valeurs
     * @param float $min     Valeur minimale de référence
     * @param float $max     Valeur maximale de référence
     * @return array Tableau des valeurs normalisées
     */
    public function prepareChartValues(array $valeurs, float $min, float $max): array
    {
        return array_map(function ($v) use ($min, $max) {
            if ($max === $min) return 0.5;
            // On s'assure que le résultat reste entre 0 et 1
            return max(0, min(1, ((float)$v['valeur'] - $min) / ($max - $min)));
        }, $valeurs);
    }

    /**
     * Charge la configuration personnalisée du tableau de bord
     *
     * Récupère la disposition des widgets (position, taille) sauvegardée
     * pour un patient donné par un médecin donné
     *
     * @param int $patientId ID du patient
     * @param int $medId     ID du médecin
     * @return array|null    Configuration (tableau) ou null si pas de config
     */
    public function getDashboardLayout(int $patientId, int $medId): ?array
    {
        $stmt = $this->db->prepare('
            SELECT layout_config FROM dashboard_layouts
            WHERE pt_id = ? AND med_id = ?
            LIMIT 1
        ');
        $stmt->execute([$patientId, $medId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        // On décode le JSON stocké en base
        $config = json_decode($row['layout_config'], true);
        return is_array($config) ? $config : null;
    }

    /**
     * Sauvegarde la disposition du tableau de bord
     *
     * Enregistre comment le médecin a organisé les graphiques pour ce patient
     * Vérifie d'abord si le médecin a bien le droit de suivre ce patient
     *
     * @param int   $patientId ID du patient
     * @param int   $medId     ID du médecin
     * @param array $config    Données de configuration (positions, tailles)
     * @return bool Vrai si la sauvegarde a réussi
     */
    public function saveDashboardLayout(int $patientId, int $medId, array $config): bool
    {
        try {
            // Vérification de sécurité : le lien médecin-patient existe-t-il ?
            $stmt = $this->db->prepare('
                SELECT COUNT(*) as count FROM suivre
                WHERE pt_id = ? AND med_id = ?
            ');
            $stmt->execute([$patientId, $medId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row || $row['count'] == 0) {
                error_log("[PATIENT_REPO] Médecin $medId ne suit pas le patient $patientId");
                return false;
            }

            $jsonConfig = json_encode($config);

            // Logs serveur pour le débogage
            error_log("[PATIENT_REPO] Sauvegarde layout - Patient: $patientId");

            // Requête "UPSERT" : Insère si nouveau, Met à jour si existe déjà
            $stmt = $this->db->prepare('
                INSERT INTO dashboard_layouts (pt_id, med_id, layout_config)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                    layout_config = VALUES(layout_config),
                    date_modification = CURRENT_TIMESTAMP
            ');

            return $stmt->execute([$patientId, $medId, $jsonConfig]);

        } catch (\Throwable $e) {
            error_log('[PATIENT_REPO] Erreur saveDashboardLayout: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Trouve des patients similaires (Algorithme KNN).
     *
     * Analyse les données du patient actuel et cherche d'autres patients
     * ayant des caractéristiques proches (âge, sexe, constantes vitales).
     *
     * Utile pour suggérer des configurations de dashboard basées sur des cas similaires
     *
     * @param int $patientId ID du patient de référence
     * @param int $medId     ID du médecin
     * @param int $k         Nombre de voisins à trouver (défaut 5)
     * @return array         Liste des patients similaires trouvés
     */
    public function findSimilarPatients(int $patientId, int $medId, int $k = 5): array
    {
        try {
            // 1. Récupérer les données du patient cible (Âge, moyennes vitales...)
            $targetStmt = $this->db->prepare('
                SELECT 
                    p.pt_id,
                    TIMESTAMPDIFF(YEAR, p.date_naissance, CURDATE()) as age,
                    p.sexe,
                    p.groupe_sanguin,
                    AVG(CASE WHEN m.type_mesure = "Tension artérielle" THEN vm.valeur END) as avg_tension,
                    AVG(CASE WHEN m.type_mesure = "Fréquence cardiaque" THEN vm.valeur END) as avg_fc,
                    AVG(CASE WHEN m.type_mesure = "Température corporelle" THEN vm.valeur END) as avg_temp,
                    AVG(CASE WHEN m.type_mesure = "Saturation en oxygène" THEN vm.valeur END) as avg_spo2
                FROM patient p
                LEFT JOIN mesures m ON p.pt_id = m.pt_id
                LEFT JOIN valeurs_mesures vm ON m.id_mesure = vm.id_mesure
                WHERE p.pt_id = ?
                GROUP BY p.pt_id
            ');
            $targetStmt->execute([$patientId]);
            $target = $targetStmt->fetch(PDO::FETCH_ASSOC);

            if (!$target) return [];

            // 2. Récupérer tous les autres patients candidats (ceux qui ont un layout sauvegardé)
            $candidatesStmt = $this->db->prepare('
                SELECT DISTINCT
                    p.pt_id,
                    TIMESTAMPDIFF(YEAR, p.date_naissance, CURDATE()) as age,
                    p.sexe,
                    p.groupe_sanguin,
                    AVG(CASE WHEN m.type_mesure = "Tension artérielle" THEN vm.valeur END) as avg_tension,
                    AVG(CASE WHEN m.type_mesure = "Fréquence cardiaque" THEN vm.valeur END) as avg_fc,
                    AVG(CASE WHEN m.type_mesure = "Température corporelle" THEN vm.valeur END) as avg_temp,
                    AVG(CASE WHEN m.type_mesure = "Saturation en oxygène" THEN vm.valeur END) as avg_spo2
                FROM patient p
                INNER JOIN suivre s ON p.pt_id = s.pt_id
                INNER JOIN dashboard_layouts dl ON p.pt_id = dl.pt_id AND dl.med_id = s.med_id
                LEFT JOIN mesures m ON p.pt_id = m.pt_id
                LEFT JOIN valeurs_mesures vm ON m.id_mesure = vm.id_mesure
                WHERE s.med_id = ? AND p.pt_id != ?
                GROUP BY p.pt_id
                HAVING COUNT(DISTINCT dl.layout_id) > 0
            ');
            $candidatesStmt->execute([$medId, $patientId]);
            $candidates = $candidatesStmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($candidates)) return [];

            // 3. Calculer les distances mathématiques entre les patients
            $distances = [];
            foreach ($candidates as $candidate) {
                $distance = 0;

                // Différence d'âge (pondérée sur 100 ans)
                $ageDiff = ($target['age'] - $candidate['age']) / 100;
                $distance += $ageDiff * $ageDiff;

                // Différence de sexe (0 = même sexe, 1 = différent)
                if ($target['sexe'] !== $candidate['sexe']) {
                    $distance += 1;
                }

                // Différence de groupe sanguin
                if ($target['groupe_sanguin'] !== $candidate['groupe_sanguin']) {
                    $distance += 0.5; // Moins important que le sexe ou l'âge
                }

                // Différence sur les constantes vitales (Tension, FC, Temp, SpO2)
                if ($target['avg_tension'] && $candidate['avg_tension']) {
                    $tensionDiff = ($target['avg_tension'] - $candidate['avg_tension']) / 80;
                    $distance += $tensionDiff * $tensionDiff;
                }
                // (Même logique pour les autres constantes...)
                if ($target['avg_fc'] && $candidate['avg_fc']) {
                    $fcDiff = ($target['avg_fc'] - $candidate['avg_fc']) / 95;
                    $distance += $fcDiff * $fcDiff;
                }
                if ($target['avg_temp'] && $candidate['avg_temp']) {
                    $tempDiff = ($target['avg_temp'] - $candidate['avg_temp']) / 5;
                    $distance += $tempDiff * $tempDiff;
                }
                if ($target['avg_spo2'] && $candidate['avg_spo2']) {
                    $spo2Diff = ($target['avg_spo2'] - $candidate['avg_spo2']) / 10;
                    $distance += $spo2Diff * $spo2Diff;
                }

                $distances[] = [
                    'pt_id' => $candidate['pt_id'],
                    'distance' => sqrt($distance)
                ];
            }

            // 4. Trier pour garder les plus proches (distance la plus petite)
            usort($distances, fn($a, $b) => $a['distance'] <=> $b['distance']);

            // Retourner les K premiers résultats
            return array_slice($distances, 0, $k);

        } catch (\Throwable $e) {
            error_log('[PATIENT_REPO] Erreur findSimilarPatients: ' . $e->getMessage());
            return [];
        }
    }
}