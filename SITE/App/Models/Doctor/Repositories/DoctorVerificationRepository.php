<?php

declare(strict_types=1);

namespace Models\Doctor\Repositories;

use Core\Database;
use Models\Doctor\Entities\Doctor;
use Models\Doctor\Interfaces\IDoctorVerificationRepository;
use PDO;

class DoctorVerificationRepository implements IDoctorVerificationRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function findByVerificationToken(string $token): ?Doctor
    {
        $stmt = $this->db->prepare('SELECT * FROM medecin WHERE email_verification_token = ? LIMIT 1');
        $stmt->execute([$token]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? new Doctor($row) : null;
    }

    public function setVerificationToken(string $email, string $token, string $expires): bool
    {
        $stmt = $this->db->prepare('UPDATE medecin SET email_verification_token = ?, email_verification_expires = ? WHERE email = ?');
        return $stmt->execute([$token, $expires, $email]);
    }

    public function verifyEmailToken(string $token): bool
    {
        $stmt = $this->db->prepare('SELECT med_id FROM medecin WHERE email_verification_token = ? AND email_verification_expires > NOW()');
        $stmt->execute([$token]);
        $id = $stmt->fetchColumn();

        if (!$id) return false;

        $update = $this->db->prepare('UPDATE medecin SET email_verified = 1, email_verification_token = NULL WHERE med_id = ?');
        return $update->execute([$id]);
    }
}