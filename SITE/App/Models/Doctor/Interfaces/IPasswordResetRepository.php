<?php

declare(strict_types=1);

namespace App\Models\Doctor\Interfaces;

/**
 * Contrat pour les opérations liées à la réinitialisation de mot de passe.
 */
interface IPasswordResetRepository
{
    public function isValidToken(string $email, string $token): bool;
    public function getEmailFromToken(string $token): ?string;
    public function markAsUsed(string $token): void;
}