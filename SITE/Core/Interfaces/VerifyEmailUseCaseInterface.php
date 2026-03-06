<?php

namespace Domain\Interfaces;

interface VerifyEmailUseCaseInterface
{
    /**
     * Tente de valider un compte via le token.
     * @return array ['success' => bool, 'message' => string, 'errors' => array]
     */
    public function execute(string $token): array;
}
