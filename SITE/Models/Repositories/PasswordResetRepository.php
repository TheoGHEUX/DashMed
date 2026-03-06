<?php

namespace Models\Repositories;

use Core\Database;
use Core\Interfaces\PasswordResetRepositoryInterface;
use PDO;

// <-- Import

class PasswordResetRepository implements PasswordResetRepositoryInterface
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function deleteExisting(string $email): void
    {
        $sql = 'DELETE FROM password_resets WHERE email = ? OR expires_at < NOW()';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);
    }

    public function create(string $email, string $tokenHash, string $expiresAt): bool
    {
        $sql = 'INSERT INTO password_resets (email, token_hash, expires_at) VALUES (?, ?, ?)';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$email, $tokenHash, $expiresAt]);
    }

    public function findEmailByToken(string $tokenHash): ?string
    {
        $sql = "SELECT email FROM password_resets WHERE token_hash = ? AND expires_at > NOW() LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tokenHash]);

        return $stmt->fetchColumn() ?: null;
    }

    public function deleteByEmail(string $email): void
    {
        $sql = "DELETE FROM password_resets WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);
    }
}