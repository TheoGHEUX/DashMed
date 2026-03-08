<?php

declare(strict_types=1);

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
        $stmt = $this->db->prepare("SELECT * FROM password_resets WHERE email = ? AND expires_at > NOW()");
        $stmt->execute([$email]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}