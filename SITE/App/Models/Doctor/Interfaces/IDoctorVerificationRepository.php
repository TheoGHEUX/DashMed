<?php

namespace App\Models\Doctor\Interfaces;

/**
 * Interface pour la gestion des tokens de vérification email médecin.
 *
 * Une interface définit un contrat : elle liste les méthodes qu'une classe doit implémenter.
 * Cela permet de garantir que plusieurs classes différentes respectent la même structure,
 * ce qui facilite la maintenance, les tests et l'évolution du code.
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
