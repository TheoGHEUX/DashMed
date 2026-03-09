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

    public function storeResetToken(string $email, string $tokenHash, string $expiresAt): void
    {
        // 1. Nettoyage : On supprime les tokens de cet email OU les tokens expirés (Maintenance)
        // C'est la fonctionnalité "Garbage Collection" de ton ancienne app
        $del = $this->db->prepare("DELETE FROM password_resets WHERE email = ? OR expires_at < NOW()");
        $del->execute([$email]);

        // 2. Insertion
        $stmt = $this->db->prepare("
            INSERT INTO password_resets (email, token_hash, expires_at, created_at) 
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$email, $tokenHash, $expiresAt]);
    }

    public function deleteResetToken(string $email): void
    {
        $stmt = $this->db->prepare('DELETE FROM password_resets WHERE email = ?');
        $stmt->execute([$email]);
    }
}