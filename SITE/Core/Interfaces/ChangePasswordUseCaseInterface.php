<?php

namespace Core\Interfaces;

interface ChangePasswordUseCaseInterface
{
    /**
     * Tente de changer le mot de passe.
     * @return array ['success' => bool, 'errors' => array, 'message' => string]
     */
    public function execute(int $userId, string $oldPassword, string $newPassword, string $confirmPassword): array;
}