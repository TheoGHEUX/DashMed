<?php

namespace Infrastructure\Persistence;

use Domain\Repositories\UserRepositoryInterface;
use Core\Database;
use PDO;

class SqlUserRepository implements UserRepositoryInterface
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function emailExists(string $email): bool
    {
        $st = $this->pdo->prepare('SELECT 1 FROM medecin WHERE LOWER(email) = LOWER(?) LIMIT 1');
        $st->execute([$email]);
        return (bool) $st->fetchColumn();
    }

    public function create(string $name, string $lastName, string $email, string $hash, string $sexe, string $specialite): bool
    {
        $st = $this->pdo->prepare(
            'INSERT INTO medecin (prenom, nom, email, mdp, sexe, specialite, compte_actif, date_creation, date_derniere_maj) ' .
            'VALUES (?, ?, ?, ?, ?, ?, 1, NOW(), NOW())'
        );
        return $st->execute([$name, $lastName, strtolower(trim($email)), $hash, $sexe, $specialite]);
    }

    public function findByEmail(string $email): ?array
    {
        $st = $this->pdo->prepare('
            SELECT med_id AS user_id, prenom AS name, nom AS last_name, email, mdp AS password, sexe, specialite, 
                   email_verified, email_verification_token, email_verification_expires
            FROM medecin WHERE LOWER(email) = LOWER(?) LIMIT 1
        ');
        $st->execute([strtolower(trim($email))]);
        $user = $st->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    public function findById(int $id): ?array
    {
        $st = $this->pdo->prepare('
            SELECT med_id AS user_id, prenom AS name, nom AS last_name, email, mdp AS password, sexe, specialite, 
                   email_verified, email_verification_token, email_verification_expires
            FROM medecin WHERE med_id = ? LIMIT 1
        ');
        $st->execute([$id]);
        $user = $st->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    public function updatePassword(int $id, string $hash): bool
    {
        $st = $this->pdo->prepare('UPDATE medecin SET mdp = ?, date_derniere_maj = NOW() WHERE med_id = ?');
        return $st->execute([$hash, $id]);
    }

    public function updateEmail(int $id, string $newEmail): bool
    {
        $st = $this->pdo->prepare('UPDATE medecin SET email = ?, date_derniere_maj = NOW() WHERE med_id = ?');
        return $st->execute([strtolower(trim($newEmail)), $id]);
    }

    public function updateEmailWithVerification(int $id, string $newEmail): ?string
    {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));

        try {
            $this->pdo->beginTransaction();
            $st = $this->pdo->prepare('
                UPDATE medecin SET email = ?, email_verified = 0, email_verification_token = ?, 
                email_verification_expires = ?, date_derniere_maj = NOW() WHERE med_id = ?
            ');
            if (!$st->execute([strtolower(trim($newEmail)), $token, $expires, $id])) {
                $this->pdo->rollBack();
                return null;
            }
            $this->pdo->commit();
            return $token;
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            return null;
        }
    }

    public function generateEmailVerificationToken(string $email): ?string
    {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));
        $st = $this->pdo->prepare('
            UPDATE medecin SET email_verification_token = ?, email_verification_expires = ?, date_derniere_maj = NOW() 
            WHERE LOWER(email) = LOWER(?)
        ');
        return $st->execute([$token, $expires, strtolower(trim($email))]) ? $token : null;
    }

    public function verifyEmailToken(string $token): bool
    {
        $st = $this->pdo->prepare('
            SELECT med_id FROM medecin WHERE email_verification_token = ? AND email_verification_expires > NOW()
            AND (email_verified = 0 OR email_verified IS NULL) LIMIT 1
        ');
        $st->execute([$token]);
        $user = $st->fetch(PDO::FETCH_ASSOC);

        if (!$user) return false;

        $st = $this->pdo->prepare('
            UPDATE medecin SET email_verified = 1, email_verification_token = NULL, email_verification_expires = NULL, 
            date_activation = NOW(), date_derniere_maj = NOW() WHERE med_id = ?
        ');
        return $st->execute([$user['med_id']]);
    }

    public function findByVerificationToken(string $token): ?array
    {
        $st = $this->pdo->prepare('
            SELECT med_id AS user_id, prenom AS name, nom AS last_name, email, email_verified, email_verification_expires
            FROM medecin WHERE email_verification_token = ? LIMIT 1
        ');
        $st->execute([$token]);
        $user = $st->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }
}