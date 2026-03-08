<?php

declare(strict_types=1);

namespace App\Models\Doctor\Interfaces;

interface ISecurityWriteRepository
{

    public function storeResetToken(string $email, string $tokenHash, string $expiresAt): void;
}