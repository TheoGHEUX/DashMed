<?php

declare(strict_types=1);

namespace App\Models\Doctor\DTOs;

/**
 * Data Transfer Object pour les données d'inscription
 */
final class RegistrationData
{
    public string $prenom;
    public string $nom;
    public string $email;
    public string $password;
    public string $confirm;
    public string $specialite;
    public ?string $sexe;

    public function __construct(array $data)
    {
        $this->prenom = trim($data['prenom'] ?? '');
        $this->nom = trim($data['nom'] ?? '');
        $this->email = trim($data['email'] ?? '');
        $this->password = $data['password'] ?? '';
        $this->confirm = $data['confirm'] ?? '';
        $this->specialite = $data['specialite'] ?? '';
        $this->sexe = $data['sexe'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'prenom' => $this->prenom,
            'nom' => $this->nom,
            'email' => $this->email,
            'password' => $this->password,
            'confirm' => $this->confirm,
            'specialite' => $this->specialite,
            'sexe' => $this->sexe,
        ];
    }
}
