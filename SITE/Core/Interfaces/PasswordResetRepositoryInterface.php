<?php

namespace Core\Interfaces;

interface PasswordResetRepositoryInterface
{
    /**
     * Supprime les anciens tokens ou ceux expirés pour un email.
     */
    public function deleteExisting(string $email): void;

    /**
     * Crée un nouveau token de réinitialisation.
     */
    public function create(string $email, string $tokenHash, string $expiresAt): bool;

    /**
     * Trouve l'email associé à un token (hashé) valide.
     * Retourne null si introuvable ou expiré.
     */
    public function findEmailByToken(string $tokenHash): ?string;

    /**
     * Supprime tous les tokens associés à un email.
     */
    public function deleteByEmail(string $email): void;
}