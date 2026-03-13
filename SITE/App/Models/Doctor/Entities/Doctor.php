<?php

declare(strict_types=1);

namespace App\Models\Doctor\Entities;

/**
 * Entité représentant un médecin (utilisateur).
 *
 * Tous les champs utiles à la fois pour la logique métier et la session utilisateur y sont stockés.
 */
final class Doctor
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
     * Instancie un médecin à partir d’un tableau associatif,
     * en prenant en charge plusieurs conventions de nommage possibles.
     */
    public function __construct(array $data)
    {
        $this->id = (int) ($data['user_id'] ?? $data['med_id'] ?? 0);
        $this->prenom = self::toString($data['name'] ?? $data['prenom'] ?? '', '');
        $this->nom = self::toString($data['last_name'] ?? $data['nom'] ?? '', '');
        $this->email = self::toString($data['email'] ?? '', '');
        $this->passwordHash = self::toString($data['password'] ?? $data['mdp'] ?? '', '');
        $this->sexe = self::toNullableString($data['sexe'] ?? null);
        $this->specialite = self::toNullableString($data['specialite'] ?? null);
        $this->emailVerified = (bool) ($data['email_verified'] ?? false);
        $this->verificationToken = self::toNullableString($data['email_verification_token'] ?? null);
        $this->verificationExpires = self::toNullableString($data['email_verification_expires'] ?? null);
    }

    private static function toString($value, string $default = ''): string
    {
        if ($value === null) {
            return $default;
        }
        return (string) $value;
    }

    private static function toNullableString($value): ?string
    {
        if ($value === null) {
            return null;
        }
        return (string) $value;
    }

    public function getId(): int { return $this->id; }
    public function getPrenom(): string { return $this->prenom; }
    public function getNom(): string { return $this->nom; }
    public function getEmail(): string { return $this->email; }
    public function getPasswordHash(): string { return $this->passwordHash; }
    public function isEmailVerified(): bool { return $this->emailVerified; }
    public function getVerificationToken(): ?string { return $this->verificationToken; }
    public function getVerificationExpires(): ?string { return $this->verificationExpires; }
    public function getSexe(): ?string { return $this->sexe; }
    public function getSpecialite(): ?string { return $this->specialite; }

    /**
     * Prépare les informations essentielles à stocker en session lors de la connexion.
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