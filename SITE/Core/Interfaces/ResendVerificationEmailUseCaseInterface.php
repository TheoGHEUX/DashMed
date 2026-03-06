<?php

namespace Domain\Interfaces;

interface ResendVerificationEmailUseCaseInterface
{
    /**
     * Relance la procédure de vérification.
     * @return array ['success' => bool, 'message' => string, 'errors' => array]
     */
    public function execute(string $email): array;
}
