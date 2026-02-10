<?php

namespace Models\Repositories;

use Core\Database;
use Models\Entities\User;
use PDO;

class UserRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function findByEmail(string $email): ?User
    {
        // Adapte les noms de colonnes selon ta vraie table (ex: users, medecin, etc.)
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $row = $stmt->fetch();

        if (!$row) return null;

        return new User(
            (int)$row['id'],
            $row['email'],
            $row['password'], // Le hash stocké en BDD
            $row['role'],
            $row['nom'] ?? null,
            $row['prenom'] ?? null
        );
    }

    public function create(string $email, string $password, string $role): bool
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, ?)");
        return $stmt->execute([$email, $hash, $role]);
    }
}
