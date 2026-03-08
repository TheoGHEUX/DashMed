<?php

declare(strict_types=1);

namespace App\Models\Doctor\Repositories;

use Core\Database;
use App\Models\Doctor\Interfaces\ISecurityWriteRepository;
use PDO;

class SecurityWriteRepository implements ISecurityWriteRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function createResetToken(string $email, string $tokenHash, string $expiresAt): void
    {
        $this->deleteResetToken($email); // Nettoyage avant insertion

        $stmt = $this->db->prepare("INSERT INTO password_resets (email, token_hash, expires_at) VALUES (?, ?, ?)");
        $stmt->execute([$email, $tokenHash, $expiresAt]);
    }

    public function deleteResetToken(string $email): void
    {
        $stmt = $this->db->prepare("DELETE FROM password_resets WHERE email = ?");
        $stmt->execute([$email]);
    }
}