<?php

declare(strict_types=1);

namespace Models\Patient\Repositories;

use Core\Database;
use Models\Patient\Entities\Patient;
use Models\Patient\Interfaces\IPatientManagementRepository;
use PDO;

class PatientManagementRepository implements IPatientManagementRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function findById(int $id): ?Patient
    {
        $stmt = $this->db->prepare('SELECT * FROM patient WHERE pt_id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? new Patient($row) : null;
    }

    public function getPatientsForDoctor(int $medId): array
    {
        $stmt = $this->db->prepare("
            SELECT p.* FROM suivre s
            JOIN patient p ON p.pt_id = s.pt_id
            WHERE s.med_id = ?
            ORDER BY p.nom, p.prenom
        ");
        $stmt->execute([$medId]);

        $patients = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $patients[] = new Patient($row);
        }
        return $patients;
    }
}