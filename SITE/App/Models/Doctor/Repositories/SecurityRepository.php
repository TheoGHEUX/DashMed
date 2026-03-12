<?php

declare(strict_types=1);

namespace App\Models\Doctor\Repositories;

use Core\Database;
use App\Models\Doctor\Interfaces\ISecurityRepository;
use PDO;

/**
 * Repository pour la gestion des tokens de sécurité (reset password).
 *
 * Un repository est une classe qui fait le lien entre le code métier et la base de données.
 * Il centralise les requêtes SQL et permet de manipuler les données de façon structurée.
 */
final class SecurityRepository implements ISecurityRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // Récupérer le dernier token pour un email
c function findResetToken(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM password_resets WHERE email = ? AND expires_at > NOW() AND used_at IS NULL ORDER BY id DESC LIMIT 1');
        $stmt->execute([$email]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data ?: null;
    }

    // Récupérer un token spécifique
    public function findResetTokenByEmailAndToken(string $email, string $tokenHash): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM password_resets WHERE email = ? AND token_hash = ? AND expires_at > NOW() AND used_at IS NULL LIMIT 1');
        $stmt->execute([$email, $tokenHash]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data ?: null;
    }

    // Stocker un nouveau token de reset, en supprimant les anciens
    public function storeResetToken(string $email, string $tokenHash, string $expiresAt): void
    {
        // Nettoie les tokens expirés OU de ce mail
        $del = $this->db->prepare("DELETE FROM password_resets WHERE email = ? OR expires_at < NOW()");
        $del->execute([$email]);

        $stmt = $this->db->prepare("
            INSERT INTO password_resets (email, token_hash, expires_at, created_at) 
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$email, $tokenHash, $expiresAt]);
    }

    // Supprimer les tokens pour cet email
    public function deleteResetToken(string $email): void
    {
        $stmt = $this->db->prepare('DELETE FROM password_resets WHERE email = ?');
        $stmt->execute([$email]);
    }
}
