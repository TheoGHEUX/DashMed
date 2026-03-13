<?php

declare(strict_types=1);

namespace App\Models\Doctor\Interfaces;

/**
 * Contrat pour l'action de renvoi d'email de validation.
 */
interface IResendVerificationEmail
{
    /**
     * Tente de renvoyer l'email de vérification à l'adresse donnée.
     *
     * @return array ['success' => bool, 'message' => string, 'error' => ?string]
     */
    public function execute(string $email): array;
}