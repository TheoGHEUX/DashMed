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
        $stmt = $this->db->prepare("SELECT * FROM patient WHERE pt_id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? $this->mapToEntity($row) : null;
    }

    public function findByDoctor(int $medId): array
    {
        $stmt = $this->db->prepare("
            SELECT p.* FROM patient p
            JOIN suivre s ON p.pt_id = s.pt_id
            WHERE s.med_id = ?
        ");
        $stmt->execute([$medId]);

        $res = [];
        while($row = $stmt->fetch()) {
            $res[] = $this->mapToEntity($row);
        }
        return $res;
    }

    // TA FONCTION DE GRAPHIQUE (Déplacée ici)
    public function getChartData(int $patientId, string $typeMesure, int $limit = 50): array
    {
        // 1. Trouver l'ID mesure
        $stmt = $this->db->prepare("SELECT id_mesure, unite FROM mesures WHERE pt_id = ? AND type_mesure = ?");
        $stmt->execute([$patientId, $typeMesure]);
        $mesure = $stmt->fetch();

        if (!$mesure) return [];

        // 2. Trouver les valeurs
        $stmt = $this->db->prepare("
            SELECT valeur, date_mesure, heure_mesure 
            FROM valeurs_mesures 
            WHERE id_mesure = ? 
            ORDER BY date_mesure DESC, heure_mesure DESC 
            LIMIT ?
        ");
        $stmt->execute([$mesure['id_mesure'], $limit]);
        $valeurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'unite' => $mesure['unite'],
            'data' => array_reverse($valeurs) // On remet dans l'ordre chrono pour le graph
        ];
    }

    private function mapToEntity($row): Patient
    {
        return new Patient(
            (int)$row['pt_id'],
            $row['nom'],
            $row['prenom'],
            $row['email'] ?? null,
            $row['sexe'] ?? null,
            $row['date_naissance'] ?? null
        );
    }
}
