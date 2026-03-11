<?php

declare(strict_types=1);

namespace App\Models\Doctor\Interfaces;

interface ISecurityRepository
{
    public function findResetToken(string $email): ?array;
    public function findResetTokenByEmailAndToken(string $email, string $tokenHash): ?array;

    public function storeResetToken(string $email, string $tokenHash, string $expiresAt): void;
    public function deleteResetToken(string $email): void;
}
