<?php

declare(strict_types=1);

namespace App\Models\Patient\Repositories;

use Core\Database;
use App\Models\Patient\Interfaces\IDashboardLayoutRepository;
use App\Models\Patient\Interfaces\IPatientSimilarityRepository;
use PDO;

/**
 * Repository unifié: layouts dashboard ET données de similarité patients.
 * Implemente IPatientSimilarityRepository et IDashboardLayoutRepository.
 */
final class DashboardLayoutRepository implements IDashboardLayoutRepository, IPatientSimilarityRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getDashboardLayout(int $patientId, int $medId): ?array
    {
        $stmt = $this->db->prepare('SELECT layout_config FROM dashboard_layouts WHERE pt_id = ? AND med_id = ? LIMIT 1');
        $stmt->execute([$patientId, $medId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? json_decode($row['layout_config'], true) : null;
    }

    public function saveDashboardLayout(int $patientId, int $medId, array $config): bool
    {
        // Vérifie qu’on suit bien ce patient avant de sauvegarder son layout
        $stmt = $this->db->prepare('SELECT 1 FROM suivre WHERE pt_id = ? AND med_id = ?');
        $stmt->execute([$patientId, $medId]);
        if (!$stmt->fetchColumn()) {
            return false;
        }

        $stmt = $this->db->prepare('
            INSERT INTO dashboard_layouts (pt_id, med_id, layout_config) VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE layout_config = VALUES(layout_config)
        ');
        return $stmt->execute([$patientId, $medId, json_encode($config)]);
    }

    /**
     * Récupère les données brut nécessaires au calcul de similarité (KNN).
     */
    public function getPatientDataForSimilarity(int $patientId): ?array
    {
        $stmt = $this->db->prepare('
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
        $stmt->execute([$patientId]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ?: null;
    }

    /**
     * Récupère tous les patients suivis, pour comparaison KNN/similarité + leur layout.
     */
    public function getCandidatesForSimilarity(int $medId, int $excludePatientId): array
    {
        $stmt = $this->db->prepare('
            SELECT DISTINCT
                p.pt_id,
                dl.layout_config,
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
            GROUP BY p.pt_id, dl.layout_config
        ');
        $stmt->execute([$medId, $excludePatientId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}