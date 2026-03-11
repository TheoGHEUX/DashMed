<?php

namespace App\Models\Doctor\Repositories;

use App\Models\Doctor\Interfaces\IDoctorVerificationRepository;
use PDO;
use Core\Database;

final class DoctorVerificationRepository implements IDoctorVerificationRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function setVerificationToken(string $email, string $token, string $expires): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE medecin
            SET email_verification_token = :token,
                email_verification_expires = :expires
            WHERE email = :email"
        );
        return $stmt->execute([
            'token'  => $token,
            'expires' => $expires,
            'email'  => $email,
        ]);
    }

    public function findByVerificationToken(string $token): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM medecin WHERE email_verification_token = :token LIMIT 1"
        );
        $stmt->execute(['token' => $token]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function verifyEmailToken(string $token): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE medecin
            SET email_verified = 1,
                email_verification_token = NULL,
                email_verification_expires = NULL
            WHERE email_verification_token = :token"
        );
        return $stmt->execute(['token' => $token]);
    }
}