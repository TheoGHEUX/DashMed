<?php

declare(strict_types=1);

namespace App\Models\Doctor\Repositories;

use Core\Database;
use App\Models\Doctor\Interfaces\IPasswordResetRepository;
use PDO;

/**
 * Repository pour la gestion des tokens de réinitialisation de mot de passe.
 *
 * Un repository est une classe qui fait le lien entre le code métier et la base de données.
 * Il centralise les requêtes SQL et permet de manipuler les données de façon structurée.
 */
final class PasswordResetRepository implements IPasswordResetRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Vérifie si un token de réinitialisation est valide pour un email.
     */
    public function isValidToken(string $email, string $token): bool
    {

        $tokenHash = hash('sha256', $token);
        $stmt = $this->db->prepare('SELECT 1 FROM password_resets WHERE email = ? AND token_hash = ? AND expires_at > NOW() AND used_at IS NULL');
        $stmt->execute([$email, $tokenHash]);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * Récupère l'email associé à un token de réinitialisation.
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
     * Marque un token de réinitialisation comme utilisé.
     */
    public function markAsUsed(string $token): void
    {
        $tokenHash = hash('sha256', $token);
        $stmt = $this->db->prepare('UPDATE password_resets SET used_at = NOW() WHERE token_hash = ?');
        $stmt->execute([$tokenHash]);
    }
}
