<?php

declare(strict_types=1);

namespace App\Models\Doctor\Interfaces;

interface ISecurityReadRepository
{
    /**
     * Trouve un token de réinitialisation selon l'email uniquement (existant pour rétrocompatibilité).
     */
    public function findResetToken(string $email): ?array;

    /**
     * Trouve un token de réinitialisation selon email ET token hash.
     * Renvoie la ligne trouvée ou null si aucun token valide.
     */
    public function findResetTokenByEmailAndToken(string $email, string $tokenHash): ?array;
}