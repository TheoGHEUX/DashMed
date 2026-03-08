<?php

declare(strict_types=1);

namespace App\Models\Doctor\Repositories;

use Core\Database;
use App\Models\Doctor\Interfaces\IDoctorWriteRepository;
use PDO;

class DoctorWriteRepository implements IDoctorWriteRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare(
            'INSERT INTO medecin (prenom, nom, email, mdp, sexe, specialite, compte_actif, date_creation) 
             VALUES (?, ?, ?, ?, ?, ?, 1, NOW())'
        );
        return $stmt->execute([
            $data['prenom'],
            $data['nom'],
            strtolower(trim($data['email'])),
            $data['password_hash'],
            $data['sexe'],
            $data['specialite']
        ]);
    }

    public function updatePassword(int $id, string $hash): bool
    {
        $stmt = $this->db->prepare('UPDATE medecin SET mdp = ?, date_derniere_maj = NOW() WHERE med_id = ?');
        return $stmt->execute([$hash, $id]);
    }

    public function updateEmail(int $id, string $email): bool
    {
        $stmt = $this->db->prepare('UPDATE medecin SET email = ?, email_verified = 0 WHERE med_id = ?');
        return $stmt->execute([$email, $id]);
    }
}