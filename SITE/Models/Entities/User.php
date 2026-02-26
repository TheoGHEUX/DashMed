<?php

namespace Models\Entities;

/**
 * Représente un utilisateur connecté à l'application (médecin).
 *
 * Cette classe rassemble toutes les informations importantes :
 * identité, connexion (email/mot de passe), et statut de vérification.
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
    private ?string $sexe;
    private ?string $specialite;
    private bool $emailVerified;
    private ?string $verificationToken;
    private ?string $verificationExpires;

    /**
     * Constructeur : Crée un utilisateur à partir de données brutes
     *
     * Remplit l'objet en gérant plusieurs noms de colonnes possibles
     * (ex: accepte 'user_id' OU 'med_id', 'password' OU 'mdp')
     * Cela permet d'utiliser cette classe peu importe comment la base de données nomme les champs
     *
     * @param array $data Données de l'utilisateur
     */
    public function __construct(array $data)
    {
        // Tente de trouver l'ID via 'user_id' ou 'med_id'
        $this->id = (int) ($data['user_id'] ?? $data['med_id'] ?? 0);

        // Gère les variations de nommage (anglais vs français)
        $this->prenom = $data['name'] ?? $data['prenom'] ?? '';
        $this->nom = $data['last_name'] ?? $data['nom'] ?? '';
        $this->email = $data['email'] ?? '';
        $this->passwordHash = $data['password'] ?? $data['mdp'] ?? '';

        $this->sexe = $data['sexe'] ?? null;
        $this->specialite = $data['specialite'] ?? null;

        // Gestion de la vérification d'email
        $this->emailVerified = (bool) ($data['email_verified'] ?? false);
        $this->verificationToken = $data['email_verification_token'] ?? null;
        $this->verificationExpires = $data['email_verification_expires'] ?? null;
    }

    // --- Méthodes de lecture (Getters) ---

    /**
     * Récupère l'identifiant unique
     * @return int
     */
    public function getId(): int { return $this->id; }

    /**
     * Récupère le prénom
     * @return string
     */
    public function getPrenom(): string { return $this->prenom; }

    /**
     * Récupère le nom de famille
     * @return string
     */
    public function getNom(): string { return $this->nom; }

    /**
     * Récupère l'adresse email
     * @return string
     */
    public function getEmail(): string { return $this->email; }

    /**
     * Récupère le mot de passe hashé
     * @return string
     */
    public function getPasswordHash(): string { return $this->passwordHash; }

    /**
     * Vérifie si l'email a été confirmé par l'utilisateur
     * @return bool Vrai si confirmé, Faux sinon
     */
    public function isEmailVerified(): bool { return $this->emailVerified; }

    /**
     * Récupère la date d'expiration du lien de vérification
     * @return string|null La date ou null
     */
    public function getVerificationExpires(): ?string { return $this->verificationExpires;}

    /**
     * Prépare les données pour la session
     *
     * Sélectionne uniquement les info nécessaires à garder en mémoire
     * une fois l'utilisateur connecté (évite de stocker le mot de passe en session)
     *
     * @return array Tableau simplifié pour $_SESSION
     */
    public function toSessionArray(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'name' => $this->prenom,
            'last_name' => $this->nom,
            'sexe' => $this->sexe,
            'specialite' => $this->specialite,
            'email_verified' => $this->emailVerified,
        ];
    }
}