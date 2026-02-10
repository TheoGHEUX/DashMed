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
}