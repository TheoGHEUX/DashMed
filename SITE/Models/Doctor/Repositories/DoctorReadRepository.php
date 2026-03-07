<?php

declare(strict_types=1);

namespace Models\Doctor\Repositories;

use Core\Database;
use Models\Doctor\Entities\Doctor;
use Models\Doctor\Interfaces\IDoctorReadRepository;
use PDO;

class DoctorReadRepository implements IDoctorReadRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function findByEmail(string $email): ?Doctor
    {
        $stmt = $this->db->prepare('SELECT * FROM medecin WHERE LOWER(email) = LOWER(?) LIMIT 1');
        $stmt->execute([trim($email)]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? new Doctor($row) : null;
    }

    public function findById(int $id): ?Doctor
    {
        $stmt = $this->db->prepare('SELECT * FROM medecin WHERE med_id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? new Doctor($row) : null;
    }

    public function emailExists(string $email): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM medecin WHERE LOWER(email) = LOWER(?) LIMIT 1');
        $stmt->execute([trim($email)]);
        return (bool) $stmt->fetchColumn();
    }
}