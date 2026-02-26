<?php

namespace Models\Repositories;

use Core\Database;
use Models\Entities\User;
use PDO;

/**
 * Dépôt Utilisateur (Médecin)
 *
 * Gère les opérations de base de données liées aux comptes médecins
 *
 * Responsable de l'authentification, de l'inscription,
 * et de la gestion des tokens de sécurité (vérification d'email)
 *
 * @package Models\Repositories
 */
class UserRepository
{
    private PDO $db;

    /**
     * Constructeur : Initialise la connexion à la base de données
     */
    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Trouve un utilisateur grâce à son adresse email
     *
     * Recherche insensible à la casse (maj/minu ignorées)
     * Mappe les colonnes de la table 'medecin' vers les propriétés attendues par l'entité User.
     *
     * @param string $email L'email à chercher
     * @return User|null L'objet User ou null si aucun compte ne correspond
     */
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

    /**
     * Trouve un utilisateur grâce à son identifiant unique
     *
     * @param int $id L'ID du médecin
     * @return User|null L'objet User ou null si introuvable
     */
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

    /**
     * Trouve un utilisateur via son jeton de vérification d'email
     *
     * Utile pour identifier qui clique sur le lien "Confirmer mon compte"
     *
     * @param string $token Le jeton reçu par email
     * @return User|null L'utilisateur correspondant ou null
     */
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

    /**
     * Vérifie si une adresse email est déjà utilisée
     *
     * Utilisé lors de l'inscription pour éviter les doublons
     *
     * @param string $email L'email à tester
     * @return bool Vrai si l'email existe déjà, Faux sinon
     */
    public function emailExists(string $email): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM medecin WHERE LOWER(email) = LOWER(?) LIMIT 1');
        $stmt->execute([trim($email)]);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * Crée un nouveau compte médecin
     *
     * Insère les informations de base. Le compte est créé actif par défaut ici (compte_actif = 1)
     *
     * @param array $data Données du formulaire (nom, prénom, email)
     * @return bool Vrai si la création a réussi
     */
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

    /**
     * Met à jour le mot de passe d'un utilisateur
     *
     * @param int    $id   L'ID du médecin
     * @param string $hash Le nouveau mot de passe (déjà haché)
     * @return bool  Succès de la mise à jour
     */
    public function updatePassword(int $id, string $hash): bool
    {
        $stmt = $this->db->prepare('UPDATE medecin SET mdp = ?, date_derniere_maj = NOW() WHERE med_id = ?');
        return $stmt->execute([$hash, $id]);
    }

    /**
     * Met à jour l'email d'un utilisateur
     *
     * Attention : Le nouvel email sera marqué comme "non vérifié" (email_verified = 0)
     * pour forcer une nouvelle validation
     *
     * @param int    $id    L'ID du médecin
     * @param string $email Le nouvel email
     * @return bool  Succès de la mise à jour
     */
    public function updateEmail(int $id, string $email): bool
    {
        $stmt = $this->db->prepare('
            UPDATE medecin
            SET email = ?, email_verified = 0, date_derniere_maj = NOW()
            WHERE med_id = ?
        ');

        return $stmt->execute([$email, $id]);
    }

    // --- SECTION VÉRIFICATION EMAIL ---

    /**
     * Définit le token de vérification pour un utilisateur
     *
     * Enregistre le token et sa date d'expiration en base de données
     * avant l'envoi de l'email de confirmation
     *
     * @param string $email   L'email du compte à vérifier
     * @param string $token   Le token généré
     * @param string $expires Date d'expiration
     * @return bool  Succès de l'opération
     */
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

    /**
     * Valide le compte d'un utilisateur via son token
     *
     * 1. Vérifie si le token est valide et non expiré
     * 2. Si oui, active le compte (email_verified = 1) et nettoie le token
     *
     * @param string $token Le token reçu
     * @return bool Vrai si le compte a été activé avec succès
     */
    public function verifyEmailToken(string $token): bool
    {
        // 1. Vérification de la validité du token
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
            return false; // Token invalide, expiré ou compte déjà vérifié
        }

        // 2. Activation du compte et nettoyage
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