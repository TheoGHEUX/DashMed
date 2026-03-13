<?php

namespace App\Models\Doctor\Interfaces;

/**
 * Contrat pour le repository de gestion des tokens de vérification email.
 */
interface IDoctorVerificationRepository
{
    /**
     * Trouve un utilisateur via le token de vérification.
     */
    public function findByVerificationToken(string $token): ?array;

    /**
     * Enregistre un token pour un email donné.
     */
    public function setVerificationToken(string $email, string $token, string $expires): bool;

    /**
     * Valide le mail à partir du token.
     */
    public function verifyEmailToken(string $token): bool;
}