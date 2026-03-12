<?php

declare(strict_types=1);

namespace App\Models\Doctor\Interfaces;

/**
 * Interface pour la gestion des tokens de réinitialisation de mot de passe.
 *
 * Une interface définit un contrat : elle liste les méthodes qu'une classe doit implémenter.
 * Cela permet de garantir que plusieurs classes différentes respectent la même structure,
 * ce qui facilite la maintenance, les tests et l'évolution du code.
 */
interface IPasswordResetRepository
{
    public function isValidToken(string $email, string $token): bool;
    public function getEmailFromToken(string $token): ?string;
    public function markAsUsed(string $token): void;
}
