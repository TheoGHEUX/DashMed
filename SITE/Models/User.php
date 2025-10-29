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

    /**
     * Création de compte avec activation requise
     * Retourne le token en clair (string) si succès, false sinon
     * @return string|false
     */
    public static function createWithActivation(string $prenom, string $nom, string $email, string $mdp, string $sexe, string $specialite)
    {
        $pdo = Database::getConnection();

        try {
            $pdo->beginTransaction();

            // Génération du token sécurisé
            $token = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $token);
            $expiresAt = date('Y-m-d H:i:s', time() + 86400); // 24 heures

            // Insertion avec compte_actif = 0 (désactivé)
            $st = $pdo->prepare('
                INSERT INTO MEDECIN (
                    prenom, nom, email, mdp, sexe, specialite,
                    compte_actif, date_creation, date_derniere_maj,
                    token_activation, token_expiration
                ) VALUES (?, ?, ?, ?, ?, ?, 0, NOW(), NOW(), ?, ?)
            ');

            $result = $st->execute([
                $prenom,
                $nom,
                strtolower(trim($email)),
                $mdp,
                $sexe,
                $specialite,
                $tokenHash,
                $expiresAt
            ]);

            if (!$result) {
                $pdo->rollBack();
                return false;
            }

            $pdo->commit();
            return $token; // Retourne le token en clair pour l'email

        } catch (\Throwable $e) {
            $pdo->rollBack();
            error_log('Erreur createWithActivation: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Vérifie si le token d'activation est valide
     * Retourne les infos du médecin (array) si valide, false sinon
     * @return array|false
     */
    public static function checkActivationToken(string $token)
    {
        $pdo = Database::getConnection();
        $tokenHash = hash('sha256', $token);

        $st = $pdo->prepare('
            SELECT med_id, prenom, nom, email, compte_actif
            FROM MEDECIN
            WHERE token_activation = ?
              AND token_expiration > NOW()
              AND compte_actif = 0
            LIMIT 1
        ');
        $st->execute([$tokenHash]);
        $medecin = $st->fetch(PDO::FETCH_ASSOC);

        return $medecin ?: false;
    }

    /**
     * Active le compte médecin
     */
    public static function activateAccount(string $token): bool
    {
        $pdo = Database::getConnection();
        $tokenHash = hash('sha256', $token);

        try {
            $pdo->beginTransaction();

            $st = $pdo->prepare('
                UPDATE MEDECIN
                SET compte_actif = 1,
                    date_activation = NOW(),
                    date_derniere_maj = NOW(),
                    token_activation = NULL,
                    token_expiration = NULL
                WHERE token_activation = ?
                  AND token_expiration > NOW()
                  AND compte_actif = 0
            ');
            $st->execute([$tokenHash]);

            if ($st->rowCount() === 0) {
                $pdo->rollBack();
                return false;
            }

            $pdo->commit();
            return true;

        } catch (\Throwable $e) {
            $pdo->rollBack();
            error_log('Erreur activateAccount: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupération pour la connexion
     * Retourne les colonnes EXACTES de la table MEDECIN
     * @return array|null
     */
    public static function findByEmail(string $email): ?array
    {
        $pdo = Database::getConnection();
        $st = $pdo->prepare('
            SELECT 
                med_id,
                prenom,
                nom,
                email,
                mdp,
                sexe,
                specialite,
                compte_actif,
                date_creation,
                date_activation,
                date_derniere_maj
            FROM MEDECIN
            WHERE LOWER(email) = LOWER(?)
            LIMIT 1
        ');
        $st->execute([strtolower(trim($email))]);
        $user = $st->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    /**
     * Récupération par ID
     * Retourne les colonnes EXACTES de la table MEDECIN
     * @return array|null
     */
    public static function findById(int $med_id): ?array
    {
        $pdo = Database::getConnection();
        $st = $pdo->prepare('
            SELECT 
                med_id,
                prenom,
                nom,
                email,
                mdp,
                sexe,
                specialite,
                compte_actif,
                date_creation,
                date_activation,
                date_derniere_maj
            FROM MEDECIN
            WHERE med_id = ?
            LIMIT 1
        ');
        $st->execute([$med_id]);
        $user = $st->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    /**
     * Met à jour le mot de passe
     */
    public static function updatePassword(int $med_id, string $mdp): bool
    {
        $pdo = Database::getConnection();
        $st = $pdo->prepare('UPDATE MEDECIN SET mdp = ?, date_derniere_maj = NOW() WHERE med_id = ?');
        return $st->execute([$mdp, $med_id]);
    }

    /**
     * Met à jour l'email
     */
    public static function updateEmail(int $med_id, string $newEmail): bool
    {
        $pdo = Database::getConnection();
        $st = $pdo->prepare('UPDATE MEDECIN SET email = ?, date_derniere_maj = NOW() WHERE med_id = ?');
        return $st->execute([strtolower(trim($newEmail)), $med_id]);
    }

    /**
     * Supprime un compte non activé (utile si l'email échoue)
     */
    public static function deleteUnactivatedAccount(string $email): bool
    {
        $pdo = Database::getConnection();
        $st = $pdo->prepare('DELETE FROM MEDECIN WHERE LOWER(email) = LOWER(?) AND compte_actif = 0');
        return $st->execute([strtolower(trim($email))]);
    }
}