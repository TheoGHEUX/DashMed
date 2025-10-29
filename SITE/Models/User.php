<?php
namespace Models;

use Core\Database;
use PDO;

final class User
{
    public static function emailExists(string $email): bool
    {
        $pdo = Database::getConnection();
        $st = $pdo->prepare('SELECT 1 FROM MEDECIN WHERE LOWER(email) = LOWER(?) LIMIT 1');
        $st->execute([$email]);
        return (bool) $st->fetchColumn();
    }

    // Création prénom + nom  + hash du mot de passe
    public static function create(string $name, string $lastName, string $email, string $hash, string $sexe, string $specialite): bool
    {
        $pdo = Database::getConnection();
        $st = $pdo->prepare(
            'INSERT INTO MEDECIN (prenom, nom, email, mdp, sexe, specialite, compte_actif, date_creation, date_derniere_maj)
         VALUES (?, ?, ?, ?, ?, ?, 1, NOW(), NOW())'
        );
        return $st->execute([$name, $lastName, strtolower(trim($email)), $hash, $sexe, $specialite]);
    }

    // Récupération pour la connexion
    public static function findByEmail(string $email): ?array
    {
        $pdo = Database::getConnection();
        $st = $pdo->prepare('
            SELECT 
                med_id   AS user_id,
                prenom   AS name,
                nom      AS last_name,
                email,
                mdp      AS password
            FROM MEDECIN
            WHERE LOWER(email) = LOWER(?)
            LIMIT 1
        ');
        $st->execute([strtolower(trim($email))]);
        $user = $st->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    public static function findById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $st = $pdo->prepare('
            SELECT 
                med_id   AS user_id,
                prenom   AS name,
                nom      AS last_name,
                email,
                mdp      AS password
            FROM MEDECIN
            WHERE med_id = ?
            LIMIT 1
        ');
        $st->execute([$id]);
        $user = $st->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    public static function updatePassword(int $id, string $hash): bool
    {
        $pdo = Database::getConnection();
        $st = $pdo->prepare('UPDATE MEDECIN SET mdp = ?, date_derniere_maj = NOW() WHERE med_id = ?');
        return $st->execute([$hash, $id]);
    }

    public static function updateEmail(int $id, string $newEmail): bool
    {
        $pdo = Database::getConnection();
        $st = $pdo->prepare('UPDATE MEDECIN SET email = ?, date_derniere_maj = NOW() WHERE med_id = ?');
        return $st->execute([strtolower(trim($newEmail)), $id]);
    }

    
}
