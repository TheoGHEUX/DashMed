<?php

declare(strict_types=1);

namespace Models\Doctor\Repositories;

use Core\Database;
use Models\Doctor\Interfaces\IPasswordResetRepository;
use PDO;

class PasswordResetRepository implements IPasswordResetRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function isValidToken(string $email, string $token): bool
    {
        // Le hashage du token se fera dans le UseCase pour être strict,
        // mais ici on suppose qu'on reçoit déjà le token hashé ou qu'on compare en SQL.
        // Pour rester simple et efficace :
        //
        //
        //Bizarre ça
        //
        //
        //
        //
        $tokenHash = hash('sha256', $token);
        $stmt = $this->db->prepare('SELECT 1 FROM password_resets WHERE email = ? AND token_hash = ? AND expires_at > NOW() AND used_at IS NULL');
        $stmt->execute([$email, $tokenHash]);
        return (bool) $stmt->fetchColumn();
    }

    public function getEmailFromToken(string $token): ?string
    {
        $tokenHash = hash('sha256', $token);
        $stmt = $this->db->prepare('SELECT email FROM password_resets WHERE token_hash = ? AND expires_at > NOW() AND used_at IS NULL');
        $stmt->execute([$tokenHash]);
        $email = $stmt->fetchColumn();
        return $email ?: null;
    }

    public function markAsUsed(string $token): void
    {
        $tokenHash = hash('sha256', $token);
        $stmt = $this->db->prepare('UPDATE password_resets SET used_at = NOW() WHERE token_hash = ?');
        $stmt->execute([$tokenHash]);
    }
}