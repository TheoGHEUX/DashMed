<?php

declare(strict_types=1);

namespace App\Models\Doctor\Interfaces;

interface IVerifyEmail
{
    /**
     * Tente de valider un compte via un token.
     * @return array ['success' => bool, 'message' => string, 'error' => ?string]
     */
    public function execute(string $token): array;
}