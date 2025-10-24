<?php
namespace Models;

use Core\Database;
use PDO;
use PDOException;

final class User
{
    /**
     * Détection simple du schéma présent: privilégie `medecin`, sinon `users`.
     * Retourne les noms de table/colonnes à utiliser.
     * @return array{table:string,id:string,first:string,last:string,email:string,password:string}
     */
    private static ?array $schema = null;

    private static function schema(): array
    {
        if (self::$schema !== null) return self::$schema;
        $pdo = Database::getConnection();
        $exists = function (string $table) use ($pdo): bool {
            $st = $pdo->prepare('SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?');
            $st->execute([$table]);
            return (bool)$st->fetchColumn();
        };
        if ($exists('medecin')) {
            return self::$schema = [
                'table'    => 'medecin',
                'id'       => 'med_id',
                'first'    => 'prenom',
                'last'     => 'nom',
                'email'    => 'email',
                'password' => 'mdp',
            ];
        }
        if ($exists('users')) {
            return self::$schema = [
                'table'    => 'users',
                'id'       => 'id',
                'first'    => 'name',
                'last'     => 'last_name',
                'email'    => 'email',
                'password' => 'password',
            ];
        }
        throw new \RuntimeException("Aucune table d'utilisateurs trouvée (medecin ou users). Créez la table requise ou ajustez le modèle.");
    }
    public static function emailExists(string $email): bool
    {
        $pdo = Database::getConnection();
        $s = self::schema();
        $sql = sprintf('SELECT 1 FROM %s WHERE LOWER(%s) = LOWER(?) LIMIT 1', $s['table'], $s['email']);
        $st = $pdo->prepare($sql);
        $st->execute([$email]);
        return (bool) $st->fetchColumn();
    }

    // Création prénom + nom  + hash du mot de passe
    public static function create(string $name, string $lastName, string $email, string $hash): bool
    {
        $pdo = Database::getConnection();
        $s = self::schema();
        if ($s['table'] === 'medecin') {
            $st = $pdo->prepare(
                'INSERT INTO medecin (prenom, nom, email, mdp, compte_actif, date_creation, date_derniere_maj)
                 VALUES (?, ?, ?, ?, 1, NOW(), NOW())'
            );
            return $st->execute([$name, $lastName, strtolower(trim($email)), $hash]);
        }
        // table users basique
        $st = $pdo->prepare('INSERT INTO users (name, last_name, email, password) VALUES (?, ?, ?, ?)');
        return $st->execute([$name, $lastName, strtolower(trim($email)), $hash]);
    }

    // Récupération pour la connexion
    public static function findByEmail(string $email): ?array
    {
        $pdo = Database::getConnection();
        $s = self::schema();
        $sql = sprintf('
            SELECT 
                %s AS user_id,
                %s AS name,
                %s AS last_name,
                %s AS email,
                %s AS password
            FROM %s
            WHERE LOWER(%s) = LOWER(?)
            LIMIT 1
        ', $s['id'], $s['first'], $s['last'], $s['email'], $s['password'], $s['table'], $s['email']);
        $st = $pdo->prepare($sql);
        $st->execute([strtolower(trim($email))]);
        $user = $st->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    public static function findById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $s = self::schema();
        $sql = sprintf('
            SELECT 
                %s AS user_id,
                %s AS name,
                %s AS last_name,
                %s AS email,
                %s AS password
            FROM %s
            WHERE %s = ?
            LIMIT 1
        ', $s['id'], $s['first'], $s['last'], $s['email'], $s['password'], $s['table'], $s['id']);
        $st = $pdo->prepare($sql);
        $st->execute([$id]);
        $user = $st->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    public static function updatePassword(int $id, string $hash): bool
    {
        $pdo = Database::getConnection();
        $s = self::schema();
        $updatedCol = ($s['table'] === 'medecin') ? 'date_derniere_maj' : 'updated_at';
        $sql = sprintf('UPDATE %s SET %s = ?, %s = NOW() WHERE %s = ?', $s['table'], $s['password'], $updatedCol, $s['id']);
        $st = $pdo->prepare($sql);
        return $st->execute([$hash, $id]);
    }

    public static function updateEmail(int $id, string $newEmail): bool
    {
        $pdo = Database::getConnection();
        $s = self::schema();
        $updatedCol = ($s['table'] === 'medecin') ? 'date_derniere_maj' : 'updated_at';
        $sql = sprintf('UPDATE %s SET %s = ?, %s = NOW() WHERE %s = ?', $s['table'], $s['email'], $updatedCol, $s['id']);
        $st = $pdo->prepare($sql);
        return $st->execute([strtolower(trim($newEmail)), $id]);
    }

    
}
