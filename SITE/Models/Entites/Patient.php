<?php

namespace Models\Entities;

class Patient
{
    private int $id;
    private string $nom;
    private string $prenom;
    private ?string $email;
    private ?string $sexe;
    private ?string $dateNaissance;

    // Constructeur simplifié
    public function __construct(int $id, string $nom, string $prenom, ?string $email = null, ?string $sexe = null, ?string $dateNaissance = null)
    {
        $this->id = $id;
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->email = $email;
        $this->sexe = $sexe;
        $this->dateNaissance = $dateNaissance;
    }

    public function getId(): int { return $this->id; }
    public function getNom(): string { return $this->nom; }
    public function getPrenom(): string { return $this->prenom; }

    // Calcul d'âge (logique métier pure, donc ça reste dans l'entité !)
    public function getAge(): ?int
    {
        if (!$this->dateNaissance) return null;
        $dob = new \DateTime($this->dateNaissance);
        $now = new \DateTime();
        return $now->diff($dob)->y;
    }
}
