<?php

declare(strict_types=1);

namespace App\Models\Doctor\Interfaces;


interface IDoctorVerificationRepository
{
    /**
     * Trouve un médecin via son token de vérification.
     * @return array|object|null
     */
    public function findByVerificationToken(string $token);

    /**
     * Enregistre un token pour un email donné.
     */
    public function setVerificationToken(string $email, string $token, string $expires): bool;

    /**
     * Valide le token (passe le flag email_verified à 1).
     */
    public function verifyEmailToken(string $token): bool;
}