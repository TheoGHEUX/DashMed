<?php

namespace Models\Entities;

/**
 * Entité Utilisateur.
 *
 * Représente un utilisateur (médecin) dans l'application.
 * Mappe les données de la base de données vers un objet PHP.
 *
 * @package Models\Entities
 */
class User
{
    private int $id;
    private string $prenom;
    private string $nom;
    private string $email;
    private string $passwordHash;
    private string $role;
    private ?string $sexe;
    private ?string $specialite;
    private bool $emailVerified;
    private ?string $verificationToken;
    private ?string $verificationExpires;

    /**
     * Constructeur.
     *
     * Initialise l'objet à partir d'un tableau associatif (souvent issu de PDO::fetch).
     * Gère les différents alias de colonnes pour éviter les erreurs d'index.
     *
     * @param array $data Données brutes de l'utilisateur
     */
    public function __construct(array $data)
    {
        // Identifiant (supporte id, user_id ou med_id)
        $this->id = (int) ($data['id'] ?? $data['user_id'] ?? $data['med_id'] ?? 0);

        // Identité
        $this->prenom = $data['prenom'] ?? $data['name'] ?? '';
        $this->nom = $data['nom'] ?? $data['last_name'] ?? '';

        // Authentification
        $this->email = $data['email'] ?? '';
        $this->passwordHash = $data['password'] ?? $data['mdp'] ?? '';
        $this->role = $data['role'] ?? 'MEDECIN'; // Valeur par défaut

        // Informations médicales
        $this->sexe = $data['sexe'] ?? null;
        $this->specialite = $data['specialite'] ?? null;

        // Vérification d'email
        // Gère le cas où la base retourne 0/1 ou false/true
        $this->emailVerified = !empty($data['email_verified']);
        $this->verificationToken = $data['verification_token'] ?? $data['email_verification_token'] ?? null;
        $this->verificationExpires = $data['verification_expires'] ?? $data['email_verification_expires'] ?? null;
    }

    // Getters

    /**
     * @return int Identifiant unique
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string Prénom
     */
    public function getPrenom(): string
    {
        return $this->prenom;
    }

    /**
     * @return string Nom de famille
     */
    public function getNom(): string
    {
        return $this->nom;
    }

    /**
     * @return string Adresse email
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return string Mot de passe haché
     */
    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    /**
     * @return string Rôle de l'utilisateur (ex: ADMIN, MEDECIN)
     */
    public function getRole(): string
    {
        return $this->role;
    }

    /**
     * @return string|null Sexe (M/F) ou null si non défini
     */
    public function getSexe(): ?string
    {
        return $this->sexe;
    }

    /**
     * @return string|null Spécialité médicale ou null si non défini
     */
    public function getSpecialite(): ?string
    {
        return $this->specialite;
    }

    /**
     * @return bool Vrai si l'email a été vérifié
     */
    public function isEmailVerified(): bool
    {
        return $this->emailVerified;
    }

    /**
     * @return string|null Token de vérification d'email
     */
    public function getVerificationToken(): ?string
    {
        return $this->verificationToken;
    }

    /**
     * @return string|null Date d'expiration du token
     */
    public function getVerificationExpires(): ?string
    {
        return $this->verificationExpires;
    }

    /**
     * Convertit l'objet en tableau pour le stockage en session.
     * Exclut les données sensibles comme le mot de passe.
     *
     * @return array
     */
    public function toSessionArray(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'prenom' => $this->prenom,
            'nom' => $this->nom,
            'role' => $this->role,
            'sexe' => $this->sexe,
            'specialite' => $this->specialite,
            'email_verified' => $this->emailVerified,
        ];
    }
}