<?php

namespace Models\Entities;

/**
 * Objet Patient (Données pures)
 */
class Patient
{
    private int $id;
    private string $nom;
    private string $prenom;
    private ?string $email;
    private ?string $sexe;
    private ?string $groupeSanguin;
    private ?string $dateNaissance;
    private ?string $telephone;
    private ?string $adresse;
    private ?string $codePostal;
    private ?string $ville;


    public function __construct(array $data)
    {
        $this->id = (int) ($data['pt_id'] ?? 0);
        $this->nom = $data['nom'] ?? '';
        $this->prenom = $data['prenom'] ?? '';
        $this->email = $data['email'] ?? null;
        $this->sexe = $data['sexe'] ?? null;
        $this->groupeSanguin = $data['groupe_sanguin'] ?? null;
        $this->dateNaissance = $data['date_naissance'] ?? null;
        $this->telephone = $data['telephone'] ?? null;
        $this->adresse = $data['adresse'] ?? null;
        $this->codePostal = $data['code_postal'] ?? null;
        $this->ville = $data['ville'] ?? null;
    }

    // Getters
    public function getId(): int { return $this->id; }
    public function getNom(): string { return $this->nom; }
    public function getPrenom(): string { return $this->prenom; }
    public function getEmail(): ?string { return $this->email; }

    public function toArray(): array
    {
        return [
            'pt_id' => $this->id,
            'nom' => $this->nom,
            'prenom' => $this->prenom,
            'email' => $this->email,
            'sexe' => $this->sexe,
            'groupe_sanguin' => $this->groupeSanguin,
            'date_naissance' => $this->dateNaissance,
            'telephone' => $this->telephone,
            'adresse' => $this->adresse,
            'code_postal' => $this->codePostal,
            'ville' => $this->ville
        ];
    }
}