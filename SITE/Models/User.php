<?php

namespace Models;

use Core\Database;
use PDO;

/**
 * Modèle de gestion des utilisateurs (médecins).
 *
 * Fournit les opérations CRUD et la gestion des tokens de vérification d'email
 * pour les comptes médecins. Tous les emails sont normalisés (trim + minuscules)
 * pour garantir l'unicité  et donc l'insensibilité à la casse.
 *
 * @package Models
 */
final class User
{
    /**
     * Vérifie si une adresse email existe déjà dans la base.
     *
     * La comparaison est insensible à la casse pour éviter les doublons.
     *
     * @param string $email Adresse email à vérifier
     * @return bool True si l'email existe déjà, false sinon
     */
    public static function emailExists(string $email): bool
    {
        $pdo = Database::getConnection();
        $st = $pdo->prepare('SELECT 1 FROM medecin WHERE LOWER(email) = LOWER(?) LIMIT 1');
        $st->execute([$email]);
        return (bool) $st->fetchColumn();
    }

    /**
     * Crée un nouveau compte médecin.
     *
     * L'email est automatiquement normalisé (trim + minuscules).  Le compte est
     * créé actif par défaut (compte_actif=1) mais l'email n'est pas vérifié.
     *
     * @param string $name Prénom du médecin
     * @param string $lastName Nom du médecin
     * @param string $email Adresse email (sera normalisée)
     * @param string $hash Hash bcrypt du mot de passe
     * @param string $sexe Sexe ('M' ou 'F')
     * @param string $specialite Spécialité médicale
     * @return bool True si la création a réussi, false sinon
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
     * Les colonnes de la base sont renommées pour correspondre à la convention
     * de l'application (med_id → user_id, prenom → name, etc.).
     * La recherche est insensible à la casse.
     *
     * @param string $email Adresse email recherchée
     * @return array|null Tableau associatif contenant user_id, name, last_name, email,
     *                    password, sexe, specialite, email_verified, email_verification_token,
     *                    email_verification_expires ou null si non trouvé
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
        $user = $st->fetch(PDO:: FETCH_ASSOC);
        return $user ?: null;
    }

    /**
     * Récupère un utilisateur par son identifiant.
     *
     * Les colonnes de la base sont renommées pour correspondre à la convention
     * de l'application.
     *
     * @param int $id Identifiant du médecin (med_id)
     * @return array|null Tableau associatif contenant user_id, name, last_name, email,
     *                    password, sexe, specialite, email_verified, email_verification_token,
     *                    email_verification_expires ou null si non trouvé
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
        return $user ?:  null;
    }

    /**
     * Met à jour le mot de passe d'un utilisateur.
     *
     * Met également à jour la date de dernière modification.
     *
     * @param int $id Identifiant du médecin
     * @param string $hash Nouveau hash bcrypt du mot de passe
     * @return bool True si la mise à jour a réussi, false sinon
     */
    public static function updatePassword(int $id, string $hash): bool
    {
        $pdo = Database::getConnection();
        $st = $pdo->prepare('UPDATE medecin SET mdp = ?, date_derniere_maj = NOW() WHERE med_id = ?');
        return $st->execute([$hash, $id]);
    }

    /**
     * Met à jour l'adresse email d'un utilisateur sans forcer une nouvelle vérification.
     *
     * L'email est automatiquement normalisé. Cette méthode NE REMET PAS A 0
     * le statut de vérification d'email.
     *
     * @param int $id Identifiant du médecin
     * @param string $newEmail Nouvelle adresse email (sera normalisée)
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
     *
     * Effectue une transaction pour garantir la cohérence des opérations :
     * - Change l'adresse email
     * - Marque l'email comme non vérifié (email_verified=0)
     * - Génère un nouveau token de vérification (64 caractères hex)
     * - Définit une expiration à 24 heures
     *
     * En cas d'erreur, la transaction est annulée et null est renvoyé.
     *
     * @param int $id Identifiant du médecin
     * @param string $newEmail Nouvelle adresse email (sera normalisée)
     * @return string|null Token de vérification généré (à envoyer par email) ou null si échec
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
     * Génère et stocke un token de vérification d'email.
     *
     * Le token est valide pendant 24 heures et composé de 64 caractères hexadécimaux.
     * Utilisé lors de l'inscription ou de la demande de renvoi du lien de vérification.
     *
     * @param string $email Adresse email du médecin
     * @return string|null Token généré (64 caractères hex) ou null si l'opération échoue
     */
    public static function generateEmailVerificationToken(string $email): ?string
    {
        $pdo = Database::getConnection();

        // Génération d'un token sécurisé
        $token = bin2hex(random_bytes(32)); // 64 caractères hexadécimaux
        $expires = date('Y-m-d H: i:s', strtotime('+24 hours'));

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
     * Vérifie que le token existe, n'est pas expiré, et que l'email n'est pas
     * déjà vérifié. En cas de succès :
     * - Marque l'email comme vérifié (email_verified=1)
     * - Supprime le token et sa date d'expiration
     * - Enregistre la date d'activation
     *
     * @param string $token Token de vérification (64 caractères hex)
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
        AND (email_verified = 0 OR email_verified IS NULL)
        LIMIT 1
    ');
        $st->execute([$token]);
        $user = $st->fetch(PDO:: FETCH_ASSOC);

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
     * Récupère un utilisateur par son token de vérification d'email.
     *
     * Utilisé pour afficher des informations sur l'utilisateur avant validation
     * du token (page de vérification, renvoi du lien, etc.).
     *
     * @param string $token Token de vérification
     * @return array|null Tableau associatif contenant user_id, name, last_name, email,
     *                    email_verified, email_verification_expires ou null si non trouvé
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