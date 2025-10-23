<?php
namespace Models;

use Core\Database;
use PDO;

final class User
{
    public static function emailExists(string $email): bool
    {
        $pdo = Database::getConnection();
        $st = $pdo->prepare('SELECT 1 FROM users WHERE LOWER(email) = LOWER(?) LIMIT 1');
        $st->execute([$email]);
        return (bool) $st->fetchColumn();
    }

    // Création prénom + nom  + hash du mot de passe
    public static function create(string $name, string $lastName, string $email, string $hash): bool
    {
        $pdo = Database::getConnection();
        $st = $pdo->prepare(
            'INSERT INTO users (name, last_name, email, password, created_at, updated_at)
             VALUES (?, ?, ?, ?, NOW(), NOW())'
        );
        return $st->execute([$name, $lastName, strtolower(trim($email)), $hash]);
    }

    // Récupération pour la connexion
    public static function findByEmail(string $email): ?array
    {
        $pdo = Database::getConnection();
        $st = $pdo->prepare('
            SELECT user_id, name, last_name, email, password
            FROM users
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
            SELECT user_id, name, last_name, email, password
            FROM users
            WHERE user_id = ?
            LIMIT 1
        ');
        $st->execute([$id]);
        $user = $st->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    public static function updatePassword(int $id, string $hash): bool
    {
        $pdo = Database::getConnection();
        $st = $pdo->prepare('UPDATE users SET password = ?, updated_at = NOW() WHERE user_id = ?');
        return $st->execute([$hash, $id]);
    }

    public static function updateEmail(int $id, string $newEmail): bool
    {
        $pdo = Database::getConnection();
        $st = $pdo->prepare('UPDATE users SET email = ?, updated_at = NOW() WHERE user_id = ?');
        return $st->execute([strtolower(trim($newEmail)), $id]);
    }

    /**
     * Créer un compte médecin avec activation requise
     * Retourne le token d'activation (non hashé) ou null en cas d'erreur
     */
    public static function createWithActivation(string $name, string $lastName, string $email, string $hash): ?string
    {
        $pdo = Database::getConnection();

        // Générer un token unique de 64 caractères
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));

        try {
            $st = $pdo->prepare(
                'INSERT INTO MEDECIN (prenom, nom, email, mdp, compte_actif, token_activation, token_expiration, date_creation)
                 VALUES (?, ?, ?, ?, 0, ?, ?, NOW())'
            );

            $success = $st->execute([
                $name,
                $lastName,
                strtolower(trim($email)),
                $hash,
                $tokenHash,
                $expiresAt
            ]);

            return $success ? $token : null;

        } catch (\Exception $e) {
            error_log('Erreur création compte : ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Activer un compte avec le token
     * Retourne true si l'activation a réussi
     */
    public static function activateAccount(string $token): bool
    {
        $pdo = Database::getConnection();
        $tokenHash = hash('sha256', $token);

        try {
            $st = $pdo->prepare('
                UPDATE MEDECIN 
                SET compte_actif = 1, 
                    date_activation = NOW(),
                    token_activation = NULL,
                    token_expiration = NULL
                WHERE token_activation = ? 
                AND token_expiration > NOW()
                AND compte_actif = 0
            ');

            $st->execute([$tokenHash]);
            return $st->rowCount() > 0;

        } catch (\Exception $e) {
            error_log('Erreur activation compte : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Vérifier si un token d'activation est valide
     * Retourne les infos du médecin ou null
     */
    public static function checkActivationToken(string $token): ?array
    {
        $pdo = Database::getConnection();
        $tokenHash = hash('sha256', $token);

        try {
            $st = $pdo->prepare('
                SELECT med_id, email, prenom, token_expiration
                FROM MEDECIN
                WHERE token_activation = ? 
                AND token_expiration > NOW()
                AND compte_actif = 0
                LIMIT 1
            ');
            $st->execute([$tokenHash]);
            $result = $st->fetch(PDO::FETCH_ASSOC);

            return $result ?: null;

        } catch (\Exception $e) {
            error_log('Erreur vérification token : ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Vérifier si le compte est actif
     */
    public static function isAccountActive(string $email): bool
    {
        $pdo = Database::getConnection();
        $st = $pdo->prepare('SELECT compte_actif FROM MEDECIN WHERE LOWER(email) = LOWER(?) LIMIT 1');
        $st->execute([strtolower(trim($email))]);
        $result = $st->fetchColumn();
        return (bool) $result;
    }

    /**
     * Régénérer un token d'activation (si l'ancien a expiré)
     */
    public static function regenerateActivationToken(string $email): ?string
    {
        $pdo = Database::getConnection();

        // Vérifier que le compte existe et n'est pas actif
        $st = $pdo->prepare('SELECT med_id, compte_actif FROM MEDECIN WHERE LOWER(email) = LOWER(?) LIMIT 1');
        $st->execute([strtolower(trim($email))]);
        $user = $st->fetch(PDO::FETCH_ASSOC);

        if (!$user || $user['compte_actif']) {
            return null; // Compte inexistant ou déjà actif
        }

        // Générer nouveau token
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));

        try {
            $st = $pdo->prepare('
                UPDATE MEDECIN 
                SET token_activation = ?,
                    token_expiration = ?
                WHERE med_id = ?
            ');

            $success = $st->execute([$tokenHash, $expiresAt, $user['med_id']]);
            return $success ? $token : null;

        } catch (\Exception $e) {
            error_log('Erreur régénération token : ' . $e->getMessage());
            return null;
        }
    }
}




