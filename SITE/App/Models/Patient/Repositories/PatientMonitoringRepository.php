<?php

declare(strict_types=1);

namespace App\Models\Patient\Repositories;

use Core\Database;
use App\Models\Patient\Interfaces\IPatientMonitoringRepository;
use PDO;

class PatientMonitoringRepository implements IPatientMonitoringRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getChartData(int $patientId, string $typeMesure, int $limit = 50): ?array
    {
        $stmt = $this->db->prepare('SELECT id_mesure, unite FROM mesures WHERE pt_id = ? AND type_mesure = ? LIMIT 1');
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

    public function getAllSeuilsForMetric(int $patientId, string $typeMesure): array
    {
        $stmt = $this->db->prepare("
            SELECT sa.statut, sa.majorant, sa.seuil
            FROM seuil_alerte sa
            JOIN mesures m ON m.id_mesure = sa.id_mesure
            WHERE m.type_mesure = ? AND m.pt_id = ?
        ");
        $stmt->execute([$typeMesure, $patientId]);

        // On retourne les données brutes. Le formatage des clés (seuil_urgent_min...)
        // pourra être fait ici car c'est du mapping SQL->Code, ce qui est acceptable dans un Repo.
        // Si tu veux être ultra-strict, retourne juste $stmt->fetchAll() et le UseCase triera.
        // Pour l'instant, garder le mapping ici est un bon compromis "Infrastructure".

        $result = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $statut = str_replace(['é', 'è'], ['e', 'e'], strtolower($row['statut']));
            $key = 'seuil_' . $statut . ((int)$row['majorant'] === 0 ? '_min' : '');
            $result[$key] = (float)$row['seuil'];
        }
        return $result;
    }

    /**
     * Génère une nouvelle valeur pour chaque mesure active du patient.
     * Basé sur une marche aléatoire (Random Walk) avec contraintes (Min/Max).
     */
    public function generateSimulationData(int $patientId): int
    {
        // 1. Définition des bornes réalistes (inspiré de generate_data_local.php)
        $ranges = [
            'Temperature' => [36.0, 40.0],
            'Tension' => [100, 160],
            'Frequence_Cardiaque' => [50, 120],
            'Frequence_Respiratoire' => [12, 25],
            'Oxygene' => [90, 100],
            'Glycemie' => [0.7, 1.8],
            'Poids' => [40, 150]
        ];

        // 2. Récupérer les types de mesures suivis par ce patient
        $stmt = $this->db->prepare("SELECT id_mesure, type_mesure FROM mesures WHERE pt_id = ?");
        $stmt->execute([$patientId]);
        $mesures = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $count = 0;
        $nowDate = date('Y-m-d');
        $nowTime = date('H:i:s');

        foreach ($mesures as $m) {
            $id = $m['id_mesure'];
            $type = $m['type_mesure'];

            // 3. Récupérer la dernière valeur connue
            $stmtLast = $this->db->prepare("
                SELECT valeur FROM valeurs_mesures 
                WHERE id_mesure = ? 
                ORDER BY date_mesure DESC, heure_mesure DESC 
                LIMIT 1
            ");
            $stmtLast->execute([$id]);
            $lastVal = $stmtLast->fetchColumn();

            // Valeur par défaut (milieu de plage) si aucune historique
            $min = $ranges[$type][0] ?? 0;
            $max = $ranges[$type][1] ?? 100;
            $base = ($lastVal !== false) ? (float)$lastVal : ($min + $max) / 2;

            // 4. Variation aléatoire (Code de generate_data_online.php amélioré)
            // Variation entre -0.5 et +0.5
            $variation = mt_rand(-5, 5) / 10;

            // Pour le poids ou la glycémie, on veut des variations plus faibles
            if ($type === 'Poids' || $type === 'Glycemie') {
                $variation = $variation / 5;
            }

            $newVal = round($base + $variation, 1);

            // 5. Garde-fou : on empêche de sortir des limites réalistes
            if ($newVal < $min) $newVal = $min + 0.1;
            if ($newVal > $max) $newVal = $max - 0.1;

            // 6. Insertion
            $insert = $this->db->prepare("
                INSERT INTO valeurs_mesures (valeur, date_mesure, heure_mesure, id_mesure)
                VALUES (?, ?, ?, ?)
            ");
            if ($insert->execute([$newVal, $nowDate, $nowTime, $id])) {
                $count++;
            }
        }

        return $count;
    }
}