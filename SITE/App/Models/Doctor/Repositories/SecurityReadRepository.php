<?php

namespace App\Models\Doctor\Repositories;

use Core\Database;
use App\Models\Doctor\Interfaces\ISecurityReadRepository;
use PDO;

class SecurityReadRepository implements ISecurityReadRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function findResetToken(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM password_resets WHERE email = ? AND expires_at > NOW() AND used_at IS NULL ORDER BY id DESC LIMIT 1');
        $stmt->execute([$email]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data ?: null;
    }

    public function findResetTokenByEmailAndToken(string $email, string $tokenHash): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM password_resets WHERE email = ? AND token_hash = ? AND expires_at > NOW() AND used_at IS NULL LIMIT 1');
        $stmt->execute([$email, $tokenHash]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data ?: null;
    }
}