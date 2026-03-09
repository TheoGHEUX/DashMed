<?php

namespace App\Models\Doctor\Repositories;

use Core\Database;
use App\Models\Doctor\Entities\Doctor;
use App\Models\Doctor\Interfaces\IDoctorRepository;
use PDO;

class DoctorRepository implements IDoctorRepository
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

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare(
            'INSERT INTO medecin (prenom, nom, email, mdp, sexe, specialite, compte_actif, email_verified, date_creation)
             VALUES (?, ?, ?, ?, ?, ?, 1, 0, NOW())'
        );
        return $stmt->execute([
            $data['prenom'],
            $data['nom'],
            strtolower(trim($data['email'])),
            $data['password_hash'],
            $data['sexe'] ?? null,
            $data['specialite'] ?? null
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

    // Bonus : méthode pour activer (en option si tu veux gérer compte_actif)
    public function activateByEmail(string $email): bool
    {
        $stmt = $this->db->prepare('UPDATE medecin SET compte_actif = 1 WHERE LOWER(email) = LOWER(?)');
        return $stmt->execute([trim($email)]);
    }
}