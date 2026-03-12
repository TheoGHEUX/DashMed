<?php

declare(strict_types=1);

namespace App\Models\Doctor\Interfaces;

/**
 * Interface pour le use case de renvoi d'email de vérification.
 *
 * Une interface définit un contrat : elle liste les méthodes qu'une classe doit implémenter.
 * Cela permet de garantir que plusieurs classes différentes respectent la même structure,
 * ce qui facilite la maintenance, les tests et l'évolution du code.
 */
interface IResendVerificationEmail
{
    /**
     * Tente de renvoyer l'email de vérification à l'adresse donnée.
     * @return array ['success' => bool, 'message' => string, 'error' => ?string]
     */
    public function execute(string $email): array;
}
