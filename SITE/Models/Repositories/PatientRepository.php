<?php

namespace Models\Repositories;

use Core\Database;
use Models\Entities\Patient;
use PDO;

class PatientRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function findById(int $id): ?Patient
    {
        $stmt = $this->db->prepare('
            SELECT * FROM patient WHERE pt_id = ? LIMIT 1
        ');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? new Patient($row) : null;
    }

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
            $patients[] = new Patient($row); // On renvoie des Objets !
        }
        return $patients;
    }


    public function getChartData(int $patientId, string $typeMesure, int $limit = 50): ?array
    {
        $stmt = $this->db->prepare('
            SELECT id_mesure, unite FROM mesures 
            WHERE pt_id = ? AND type_mesure = ? LIMIT 1
        ');
        $stmt->execute([$patientId, $typeMesure]);
        $mesure = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$mesure) return null;

        $stmt = $this->db->prepare('
            SELECT valeur, date_mesure, heure_mesure 
            FROM valeurs_mesures 
            WHERE id_mesure = ? 
            ORDER BY date_mesure DESC, heure_mesure DESC 
            LIMIT ?
        ');
        $stmt->execute([$mesure['id_mesure'], $limit]);
        $valeurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'id_mesure' => $mesure['id_mesure'],
            'type_mesure' => $typeMesure,
            'unite' => $mesure['unite'],
            'valeurs' => array_reverse($valeurs)
        ];
    }

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


    public function prepareChartValues(array $valeurs, float $min, float $max): array
    {
        return array_map(function ($v) use ($min, $max) {
            if ($max === $min) return 0.5;
            return max(0, min(1, ((float)$v['valeur'] - $min) / ($max - $min)));
        }, $valeurs);
    }

    /**
     * Récupère l'agencement du dashboard pour un patient spécifique
     * 
     * @param int $patientId ID du patient
     * @param int $medId ID du médecin
     * @return array|null Configuration de l'agencement ou null si aucun agencement personnalisé
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

        // Décoder le JSON
        $config = json_decode($row['layout_config'], true);
        return is_array($config) ? $config : null;
    }

    /**
     * Sauvegarde l'agencement du dashboard pour un patient
     * 
     * @param int $patientId ID du patient
     * @param int $medId ID du médecin
     * @param array $config Configuration de l'agencement {visible: [...], sizes: {...}}
     * @return bool True si la sauvegarde a réussi
     */
    public function saveDashboardLayout(int $patientId, int $medId, array $config): bool
    {
        try {
            // Vérifier que le médecin suit bien ce patient
            $stmt = $this->db->prepare('
                SELECT COUNT(*) as count FROM suivre
                WHERE pt_id = ? AND med_id = ?
            ');
            $stmt->execute([$patientId, $medId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row || $row['count'] == 0) {
                // Le médecin ne suit pas ce patient
                error_log("[PATIENT_REPO] Médecin $medId ne suit pas le patient $patientId");
                return false;
            }

            // Encoder la configuration en JSON
            $jsonConfig = json_encode($config);
            
            error_log("[PATIENT_REPO] Sauvegarde layout - Patient: $patientId, Médecin: $medId");
            error_log("[PATIENT_REPO] Config JSON: " . $jsonConfig);
            error_log("[PATIENT_REPO] JSON valide: " . (json_last_error() === JSON_ERROR_NONE ? 'OUI' : 'NON - ' . json_last_error_msg()));

            // Insérer ou mettre à jour l'agencement (UPSERT)
            $stmt = $this->db->prepare('
                INSERT INTO dashboard_layouts (pt_id, med_id, layout_config)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                    layout_config = VALUES(layout_config),
                    date_modification = CURRENT_TIMESTAMP
            ');

            $result = $stmt->execute([$patientId, $medId, $jsonConfig]);
            error_log("[PATIENT_REPO] Résultat execute: " . ($result ? 'SUCCESS' : 'FAILED'));
            
            return $result;
        } catch (\Throwable $e) {
            error_log('[PATIENT_REPO] Erreur saveDashboardLayout: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Trouve des patients similaires en utilisant l'algorithme KNN
     * @param int $patientId ID du patient de référence
     * @param int $medId ID du médecin
     * @param int $k Nombre de voisins à trouver
     * @return array Liste des patients similaires avec leur distance
     */
    public function findSimilarPatients(int $patientId, int $medId, int $k = 5): array
    {
        try {
            // Récupérer les caractéristiques du patient cible
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

            if (!$target) {
                return [];
            }

            // Récupérer tous les autres patients du médecin avec un layout enregistré
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

            error_log("[KNN] Patient cible: #$patientId, Médecin: #$medId");
            error_log("[KNN] Nombre de candidats trouvés: " . count($candidates));
            
            if (empty($candidates)) {
                error_log("[KNN] Aucun patient candidat trouvé avec un layout enregistré");
                return [];
            }

            // Calculer les distances euclidiennes normalisées
            $distances = [];
            foreach ($candidates as $candidate) {
                $distance = 0;

                // Distance d'âge (normalisée sur 100 ans)
                $ageDiff = ($target['age'] - $candidate['age']) / 100;
                $distance += $ageDiff * $ageDiff;

                // Distance de sexe (0 ou 1)
                if ($target['sexe'] !== $candidate['sexe']) {
                    $distance += 1;
                }

                // Distance de groupe sanguin (0 ou 1)
                if ($target['groupe_sanguin'] !== $candidate['groupe_sanguin']) {
                    $distance += 0.5; // Poids réduit
                }

                // Distances des moyennes de constantes vitales (normalisées)
                if ($target['avg_tension'] && $candidate['avg_tension']) {
                    $tensionDiff = ($target['avg_tension'] - $candidate['avg_tension']) / 80; // Plage ~80-160
                    $distance += $tensionDiff * $tensionDiff;
                }

                if ($target['avg_fc'] && $candidate['avg_fc']) {
                    $fcDiff = ($target['avg_fc'] - $candidate['avg_fc']) / 95; // Plage ~35-130
                    $distance += $fcDiff * $fcDiff;
                }

                if ($target['avg_temp'] && $candidate['avg_temp']) {
                    $tempDiff = ($target['avg_temp'] - $candidate['avg_temp']) / 5; // Plage ~35-40
                    $distance += $tempDiff * $tempDiff;
                }

                if ($target['avg_spo2'] && $candidate['avg_spo2']) {
                    $spo2Diff = ($target['avg_spo2'] - $candidate['avg_spo2']) / 10; // Plage ~90-100
                    $distance += $spo2Diff * $spo2Diff;
                }

                $distances[] = [
                    'pt_id' => $candidate['pt_id'],
                    'distance' => sqrt($distance)
                ];
            }

            // Trier par distance croissante et prendre les k plus proches
            usort($distances, fn($a, $b) => $a['distance'] <=> $b['distance']);
            $result = array_slice($distances, 0, $k);
            
            error_log("[KNN] Patients similaires trouvés: " . count($result));
            if (!empty($result)) {
                error_log("[KNN] Patient le plus proche: #" . $result[0]['pt_id'] . " (distance: " . round($result[0]['distance'], 2) . ")");
            }
            
            return $result;

        } catch (\Throwable $e) {
            error_log('[PATIENT_REPO] Erreur findSimilarPatients: ' . $e->getMessage());
            return [];
        }
    }
}