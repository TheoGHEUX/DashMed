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
        $stmt = $this->db->prepare('
            SELECT 
                med_id AS user_id,
                prenom AS name,
                nom AS last_name,
                email,
                mdp AS password,
                sexe,
                specialite,
                email_verified,
                email_verification_token,
                email_verification_expires
            FROM medecin
            WHERE LOWER(email) = LOWER(?)
            LIMIT 1
        ');
        $stmt->execute([trim($email)]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? new User($row) : null;
    }

    public function findById(int $id): ?User
    {
        $stmt = $this->db->prepare('
            SELECT 
                med_id AS user_id,
                prenom AS name,
                nom AS last_name,
                email,
                mdp AS password,
                sexe,
                specialite,
                email_verified,
                email_verification_token,
                email_verification_expires
            FROM medecin
            WHERE med_id = ?
            LIMIT 1
        ');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? new User($row) : null;
    }

    public function findByVerificationToken(string $token): ?User
    {
        $stmt = $this->db->prepare('
            SELECT 
                med_id AS user_id,
                prenom AS name,
                nom AS last_name,
                email,
                mdp AS password,
                sexe,
                specialite,
                email_verified,
                email_verification_token,
                email_verification_expires
            FROM medecin
            WHERE email_verification_token = ?
            LIMIT 1
        ');
        $stmt->execute([$token]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? new User($row) : null;
    }

    public function emailExists(string $email): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM medecin WHERE LOWER(email) = LOWER(?) LIMIT 1');
        $stmt->execute([trim($email)]);
        return (bool) $stmt->fetchColumn();
    }


    public function create(array $data): bool
    {
        $stmt = $this->db->prepare(
            'INSERT INTO medecin (prenom, nom, email, mdp, sexe, specialite, compte_actif, date_creation, date_derniere_maj) 
             VALUES (?, ?, ?, ?, ?, ?, 1, NOW(), NOW())'
        );
        return $stmt->execute([
            $data['prenom'],
            $data['nom'],
            strtolower(trim($data['email'])),
            $data['password_hash'],
            $data['sexe'],
            $data['specialite']
        ]);
    }

    public function updatePassword(int $id, string $hash): bool
    {
        $stmt = $this->db->prepare('UPDATE medecin SET mdp = ?, date_derniere_maj = NOW() WHERE med_id = ?');
        return $stmt->execute([$hash, $id]);
    }

    public function updateEmail(int $id, string $email): bool
    {
        $stmt = $this->db->prepare('
        UPDATE medecin
        SET email = ?, email_verified = 0, date_derniere_maj = NOW()
        WHERE med_id = ?
    ');

        return $stmt->execute([$email, $id]);
    }

    // VÉRIFICATION EMAIL

    public function setVerificationToken(string $email, string $token, string $expires): bool
    {
        $stmt = $this->db->prepare('
            UPDATE medecin 
            SET email_verification_token = ?, 
                email_verification_expires = ?,
                date_derniere_maj = NOW()
            WHERE LOWER(email) = LOWER(?)
        ');
        return $stmt->execute([$token, $expires, trim($email)]);
    }

    public function verifyEmailToken(string $token): bool
    {
        $stmt = $this->db->prepare('
            SELECT med_id 
            FROM medecin 
            WHERE email_verification_token = ? 
            AND email_verification_expires > NOW()
            AND (email_verified = 0 OR email_verified IS NULL)
            LIMIT 1
        ');
        $stmt->execute([$token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return false;
        }

        $update = $this->db->prepare('
            UPDATE medecin 
            SET email_verified = 1,
                email_verification_token = NULL,
                email_verification_expires = NULL,
                date_activation = NOW(),
                date_derniere_maj = NOW()
            WHERE med_id = ?
        ');

        return $update->execute([$user['med_id']]);
    }
}