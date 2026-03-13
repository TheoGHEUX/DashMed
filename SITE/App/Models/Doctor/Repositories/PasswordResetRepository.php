<?php

declare(strict_types=1);

namespace App\Models\Doctor\Repositories;

use Core\Database;
use App\Models\Doctor\Interfaces\IPasswordResetRepository;
use PDO;

/**
 * Repository pour la gestion du reset de mot de passe (table password_resets).
 */
final class PasswordResetRepository implements IPasswordResetRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Vérifie la validité d’un token pour un email donné
     * (et si le token n’est pas expiré/utilisé).
     */
    public function isValidToken(string $email, string $token): bool
    {
        $tokenHash = hash('sha256', $token);
        $stmt = $this->db->prepare('SELECT 1 FROM password_resets WHERE email = ? AND token_hash = ? AND expires_at > NOW() AND used_at IS NULL');
        $stmt->execute([$email, $tokenHash]);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * Retourne l’email lié à un token de reset encore valide.
     */
    public function getEmailFromToken(string $token): ?string
    {
        $tokenHash = hash('sha256', $token);
        $stmt = $this->db->prepare('SELECT email FROM password_resets WHERE token_hash = ? AND expires_at > NOW() AND used_at IS NULL');
        $stmt->execute([$tokenHash]);
        $email = $stmt->fetchColumn();
        return $email ?: null;
    }

    /**
     * Marque un token de reset comme utilisé.
     */
    public function markAsUsed(string $token): void
    {
        $tokenHash = hash('sha256', $token);
        $stmt = $this->db->prepare('UPDATE password_resets SET used_at = NOW() WHERE token_hash = ?');
        $stmt->execute([$tokenHash]);
    }
}