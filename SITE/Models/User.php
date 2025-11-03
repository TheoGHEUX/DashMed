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

    public static function create(string $name, string $lastName, string $email, string $hash, string $sexe, string $specialite): bool
    {
        $pdo = Database::getConnection();
        $st = $pdo->prepare(
            'INSERT INTO MEDECIN (prenom, nom, email, mdp, sexe, specialite, compte_actif, date_creation, date_derniere_maj)
             VALUES (?, ?, ?, ?, ?, ?, 1, NOW(), NOW())'
        );
        return $st->execute([$name, $lastName, strtolower(trim($email)), $hash, $sexe, $specialite]);
    }

    public static function findByEmail(string $email): ?array
    {
        $pdo = Database::getConnection();
        $st = $pdo->prepare('
            SELECT 
                med_id      AS user_id,
                prenom      AS name,
                nom         AS last_name,
                email,
                mdp         AS password,
                sexe,
                specialite,
                email_verified,
                email_verification_token,
                email_verification_expires
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
                med_id      AS user_id,
                prenom      AS name,
                nom         AS last_name,
                email,
                mdp         AS password,
                sexe,
                specialite,
                email_verified,
                email_verification_token,
                email_verification_expires
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

    /**
     * Génère et stocke un token de vérification d'email (valide 24h)
     */
    public static function generateEmailVerificationToken(string $email): ?string
    {
        $pdo = Database::getConnection();
        
        // Génération d'un token sécurisé
        $token = bin2hex(random_bytes(32)); // 64 caractères hexadécimaux
        $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        $st = $pdo->prepare('
            UPDATE MEDECIN 
            SET email_verification_token = ?, 
                email_verification_expires = ?,
                date_derniere_maj = NOW()
            WHERE LOWER(email) = LOWER(?)
        ');
        
        if ($st->execute([$token, $expires, strtolower(trim($email))])) {
            return $token;
        }
        
        return null;
    }

    /**
     * Vérifie un token de vérification d'email et active le compte
     */
    public static function verifyEmailToken(string $token): bool
    {
        $pdo = Database::getConnection();
        
        // Recherche du token valide (non expiré)
        $st = $pdo->prepare('
            SELECT med_id 
            FROM MEDECIN 
            WHERE email_verification_token = ? 
            AND email_verification_expires > NOW()
            AND email_verified = 0
            LIMIT 1
        ');
        $st->execute([$token]);
        $user = $st->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            return false;
        }
        
        // Activation du compte et suppression du token
        $st = $pdo->prepare('
            UPDATE MEDECIN 
            SET email_verified = 1,
                email_verification_token = NULL,
                email_verification_expires = NULL,
                date_activation = NOW(),
                date_derniere_maj = NOW()
            WHERE med_id = ?
        ');
        
        return $st->execute([$user['med_id']]);
    }

    /**
     * Trouve un utilisateur par son token de vérification
     */
    public static function findByVerificationToken(string $token): ?array
    {
        $pdo = Database::getConnection();
        $st = $pdo->prepare('
            SELECT 
                med_id      AS user_id,
                prenom      AS name,
                nom         AS last_name,
                email,
                email_verified,
                email_verification_expires
            FROM MEDECIN
            WHERE email_verification_token = ?
            LIMIT 1
        ');
        $st->execute([$token]);
        $user = $st->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }
}