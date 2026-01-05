<?php

namespace Models;

use Core\Database;
use PDO;

/**
 * Class User
 *
 * Représente les opérations basiques liées aux utilisateurs (table medecin).
 * Fournit des méthodes pour la création, la recherche, la mise à jour et la gestion
 * des tokens de vérification d'email.
 *
 * Notes :
 * - Toutes les méthodes utilisent Database::getConnection() pour obtenir un PDO.
 * - Les retours utilisent des types scalaires simples (bool, string|null, array|null).
 *
 * @package Models
 * @author  DashMed
 * @version 1.0
 */
final class User
{
    /**
     * Vérifie si une adresse email existe déjà (insensible à la casse).
     *
     * @param string $email Email à tester
     * @return bool True si l'email existe, false sinon
     */
    public static function emailExists(string $email): bool
    {
        $pdo = Database::getConnection();
        $st = $pdo->prepare('SELECT 1 FROM medecin WHERE LOWER(email) = LOWER(?) LIMIT 1');
        $st->execute([$email]);
        return (bool) $st->fetchColumn();
    }

    /**
     * Crée un nouvel utilisateur (médecin).
     *
     * @param string $name      Prénom
     * @param string $lastName  Nom
     * @param string $email     Email (sera trim + strtolower)
     * @param string $hash      Hash du mot de passe
     * @param string $sexe      Sexe (ex: 'M'/'F')
     * @param string $specialite Spécialité
     * @return bool True si l'insertion a réussi, false sinon
     */
    public static function create(
        string $name,
        string $lastName,
        string $email,
        string $hash,
        string $sexe,
        string $specialite
    ): bool {
        $pdo = Database::getConnection();
        $st = $pdo->prepare(
            'INSERT INTO medecin (prenom, nom, email, mdp, sexe, specialite, '
            . 'compte_actif, date_creation, date_derniere_maj) '
            . 'VALUES (?, ?, ?, ?, ?, ?, 1, NOW(), NOW())'
        );
        return $st->execute([$name, $lastName, strtolower(trim($email)), $hash, $sexe, $specialite]);
    }

    /**
     * Récupère un utilisateur par son adresse email.
     *
     * @param string $email Email recherché
     * @return array|null Tableau associatif de l'utilisateur ou null si non trouvé
     */
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
            FROM medecin
            WHERE LOWER(email) = LOWER(?)
            LIMIT 1
        ');
        $st->execute([strtolower(trim($email))]);
        $user = $st->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    /**
     * Récupère un utilisateur par son identifiant.
     *
     * @param int $id Identifiant utilisateur
     * @return array|null Tableau associatif de l'utilisateur ou null si non trouvé
     */
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
            FROM medecin
            WHERE med_id = ?
            LIMIT 1
        ');
        $st->execute([$id]);
        $user = $st->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    /**
     * Met à jour le mot de passe d'un utilisateur.
     *
     * @param int    $id   Identifiant utilisateur
     * @param string $hash Nouveau hash du mot de passe
     * @return bool True si la mise à jour a réussi, false sinon
     */
    public static function updatePassword(int $id, string $hash): bool
    {
        $pdo = Database::getConnection();
        $st = $pdo->prepare('UPDATE medecin SET mdp = ?, date_derniere_maj = NOW() WHERE med_id = ?');
        return $st->execute([$hash, $id]);
    }

    /**
     * Met à jour l'adresse email d'un utilisateur.
     *
     * @param int    $id       Identifiant utilisateur
     * @param string $newEmail Nouvelle adresse email (sera trim + strtolower)
     * @return bool True si la mise à jour a réussi, false sinon
     */
    public static function updateEmail(int $id, string $newEmail): bool
    {
        $pdo = Database::getConnection();
        $st = $pdo->prepare('UPDATE medecin SET email = ?, date_derniere_maj = NOW() WHERE med_id = ?');
        return $st->execute([strtolower(trim($newEmail)), $id]);
    }

    /**
     * Met à jour l'email en forçant une nouvelle vérification.
     * Retourne le token à envoyer ou null en cas d'échec.
     */
    public static function updateEmailWithVerification(int $id, string $newEmail): ?string
    {
        $pdo = Database::getConnection();
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));

        try {
            $pdo->beginTransaction();
            $st = $pdo->prepare('
                UPDATE medecin
                SET email = ?,
                    email_verified = 0,
                    email_verification_token = ?,
                    email_verification_expires = ?,
                    date_derniere_maj = NOW()
                WHERE med_id = ?
            ');

            if (!$st->execute([strtolower(trim($newEmail)), $token, $expires, $id])) {
                $pdo->rollBack();
                return null;
            }

            $pdo->commit();
            return $token;
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log(sprintf('[USER][EMAIL_CHANGE] %s in %s:%d', $e->getMessage(), $e->getFile(), $e->getLine()));
            return null;
        }
    }

    /**
     * Génère et stocke un token de vérification d'email (valide 24h).
     *
     * @param string $email Email de l'utilisateur
     * @return string|null Token généré (64 hex chars) ou null si l'opération échoue
     */
    public static function generateEmailVerificationToken(string $email): ?string
    {
        $pdo = Database::getConnection();

        // Génération d'un token sécurisé
        $token = bin2hex(random_bytes(32)); // 64 caractères hexadécimaux
        $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));

        $st = $pdo->prepare('
            UPDATE medecin 
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
     * Vérifie un token de vérification d'email et active le compte.
     *
     * @param string $token Token de vérification
     * @return bool True si le token est valide et l'activation réussie, false sinon
     */
    public static function verifyEmailToken(string $token): bool
    {
        $pdo = Database::getConnection();

        // Recherche du token valide (non expiré)
        $st = $pdo->prepare('
        SELECT med_id 
        FROM medecin 
        WHERE email_verification_token = ? 
        AND email_verification_expires > NOW()
        AND (email_verified = 0 OR email_verified IS NULL)  -- ← Correction ici
        LIMIT 1
    ');
        $st->execute([$token]);
        $user = $st->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return false;
        }

        // Activation du compte et suppression du token
        $st = $pdo->prepare('
        UPDATE medecin 
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
     * Trouve un utilisateur par son token de vérification.
     *
     * @param string $token Token de vérification
     * @return array|null Tableau associatif de l'utilisateur ou null si non trouvé
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
            FROM medecin
            WHERE email_verification_token = ?
            LIMIT 1
        ');
        $st->execute([$token]);
        $user = $st->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }
}
