<?php

declare(strict_types=1);

namespace App\Models\Doctor\Repositories;

use App\Models\Doctor\Interfaces\IDoctorVerificationRepository;
use Core\Database;
use PDO;

class DoctorVerificationRepository implements IDoctorVerificationRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Enregistre le token (Implémentation de setVerificationToken)
     */
    public function setVerificationToken(string $email, string $token, string $expires): bool
    {
        // On supprime d'abord les anciens tokens pour cet email (optionnel mais propre)
        $stmtDel = $this->db->prepare("DELETE FROM email_verifications WHERE email = ?");
        $stmtDel->execute([$email]);

        // On insère le nouveau
        $stmt = $this->db->prepare("
            INSERT INTO email_verifications (email, token, expires_at, created_at)
            VALUES (:email, :token, :expires, NOW())
        ");

        return $stmt->execute([
            ':email' => $email,
            ':token' => $token,
            ':expires' => $expires
        ]);
    }

    /**
     * Trouve un médecin via son token (Implémentation requise par l'interface)
     */
    public function findByVerificationToken(string $token)
    {
        $stmt = $this->db->prepare("SELECT * FROM email_verifications WHERE token = ? LIMIT 1");
        $stmt->execute([$token]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Valide le token (Implémentation requise par l'interface)
     */
    public function verifyEmailToken(string $token): bool
    {
        // 1. Récupérer l'email associé au token
        $data = $this->findByVerificationToken($token);
        if (!$data) return false;

        // 2. Mettre à jour la table medecin
        // Note: Vérifie le nom de ta colonne (is_verified, email_verified_at, etc.)
        $stmt = $this->db->prepare("UPDATE medecin SET email_verified = 1 WHERE email = ?");
        $res = $stmt->execute([$data['email']]);

        // 3. Supprimer le token utilisé
        if ($res) {
            $del = $this->db->prepare("DELETE FROM email_verifications WHERE token = ?");
            $del->execute([$token]);
        }

        return $res;
    }
}