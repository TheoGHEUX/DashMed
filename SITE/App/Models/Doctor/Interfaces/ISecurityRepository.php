<?php

declare(strict_types=1);

namespace App\Models\Doctor\Interfaces;

/**
 * Interface pour la gestion des tokens de sécurité (reset password).
 *
 * Une interface définit un contrat : elle liste les méthodes qu'une classe doit implémenter.
 * Cela permet de garantir que plusieurs classes différentes respectent la même structure,
 * ce qui facilite la maintenance, les tests et l'évolution du code.
 */
interface ISecurityRepository
{
    public function findResetToken(string $email): ?array;
    public function findResetTokenByEmailAndToken(string $email, string $tokenHash): ?array;

    public function storeResetToken(string $email, string $tokenHash, string $expiresAt): void;
    public function deleteResetToken(string $email): void;
}
