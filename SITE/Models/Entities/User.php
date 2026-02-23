<?php

namespace Models\Entities;

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

    public function __construct(array $data)
    {
        $this->id = (int) ($data['user_id'] ?? $data['med_id'] ?? 0);
        $this->prenom = $data['name'] ?? $data['prenom'] ?? '';
        $this->nom = $data['last_name'] ?? $data['nom'] ?? '';
        $this->email = $data['email'] ?? '';
        $this->passwordHash = $data['password'] ?? $data['mdp'] ?? '';
        $this->sexe = $data['sexe'] ?? null;
        $this->specialite = $data['specialite'] ?? null;
        $this->emailVerified = (bool) ($data['email_verified'] ?? false);
        $this->verificationToken = $data['email_verification_token'] ?? null;
        $this->verificationExpires = $data['email_verification_expires'] ?? null;
    }

    // Getters
    public function getId(): int { return $this->id; }
    public function getPrenom(): string { return $this->prenom; }
    public function getNom(): string { return $this->nom; }
    public function getEmail(): string { return $this->email; }
    public function getPasswordHash(): string { return $this->passwordHash; }
    public function isEmailVerified(): bool { return $this->emailVerified; }
    public function getVerificationExpires(): ?string { return $this->verificationExpires;}

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