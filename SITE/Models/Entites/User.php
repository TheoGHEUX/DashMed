<?php

namespace Models\Entities;

class User
{
    private int $id;
    private string $email;
    private string $passwordHash; // Le mot de passe crypté
    private string $role;         // ex: 'medecin', 'admin'
    private ?string $nom;
    private ?string $prenom;

    public function __construct(int $id, string $email, string $passwordHash, string $role, ?string $nom = null, ?string $prenom = null)
    {
        $this->id = $id;
        $this->email = $email;
        $this->passwordHash = $passwordHash;
        $this->role = $role;
        $this->nom = $nom;
        $this->prenom = $prenom;
    }

    // Getters
    public function getId(): int { return $this->id; }
    public function getEmail(): string { return $this->email; }
    public function getRole(): string { return $this->role; }
    public function getNom(): ?string { return $this->nom; }
    public function getPrenom(): ?string { return $this->prenom; }

    // Pour vérifier le mot de passe (logique métier)
    public function verifyPassword(string $passwordClair): bool
    {
        return password_verify($passwordClair, $this->passwordHash);
    }
}
