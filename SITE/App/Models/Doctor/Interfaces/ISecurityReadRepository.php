<?php

declare(strict_types=1);

namespace App\Models\Doctor\Interfaces;

interface ISecurityReadRepository
{
    public function findResetToken(string $email): ?array;
}