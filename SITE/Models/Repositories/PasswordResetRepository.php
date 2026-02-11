<?php

namespace Models\Repositories;

use Core\Database;
use PDO;

class PasswordResetRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Vérifie si un token est valide pour un email donné.
     */
    public function isValidToken(string $email, string $token): bool
    {
        if ($email === '' || $token === '') return false;

        $tokenHash = hash('sha256', $token);
        $stmt = $this->db->prepare('
            SELECT 1 FROM password_resets
            WHERE LOWER(email) = LOWER(?)
              AND token_hash = ?
              AND expires_at > NOW()
              AND used_at IS NULL
            LIMIT 1
        ');
        $stmt->execute([$email, $tokenHash]);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * Récupère l'email associé à un token valide (et verrouille la ligne).
     */
    public function getEmailFromToken(string $token): ?string
    {
        $tokenHash = hash('sha256', $token);
        $stmt = $this->db->prepare('
            SELECT email FROM password_resets
            WHERE token_hash = ?
              AND expires_at > NOW()
              AND used_at IS NULL
            LIMIT 1
            FOR UPDATE
        ');
        $stmt->execute([$tokenHash]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? strtolower(trim($row['email'])) : null;
    }

    /**
     * Marque un token comme utilisé.
     */
    public function markAsUsed(string $token): void
    {
        $tokenHash = hash('sha256', $token);
        $stmt = $this->db->prepare('
            UPDATE password_resets 
            SET used_at = NOW() 
            WHERE token_hash = ? 
            AND used_at IS NULL
        ');
        $stmt->execute([$tokenHash]);
    }
}