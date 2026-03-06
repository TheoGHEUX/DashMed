<?php

namespace Domain\UseCases\Auth;

use Core\Interfaces\PasswordResetRepositoryInterface;
use Core\Interfaces\UserRepositoryInterface;
use Core\Mailer;

class RequestPasswordResetUseCase
{
    private UserRepositoryInterface $userRepo;
    private PasswordResetRepositoryInterface $pwdRepo;

    public function __construct(UserRepositoryInterface $userRepo, PasswordResetRepositoryInterface $pwdRepo)
    {
        $this->userRepo = $userRepo;
        $this->pwdRepo = $pwdRepo;
    }

    public function execute(string $email): void
    {
        // 1. Vérifier si l'utilisateur existe
        $user = $this->userRepo->findByEmail($email);

        if (!$user) {
            return;
        }

        // 2. Nettoyer les anciens tokens
        $this->pwdRepo->deleteExisting($email);

        // 3. Générer le nouveau token
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1h

        // 4. Sauvegarder
        $this->pwdRepo->create($email, $tokenHash, $expiresAt);

        // 5. Construire le lien
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['SERVER_NAME'] ?? 'localhost';

        $resetUrl = $scheme . '://' . $host . '/reset-password?token='
            . urlencode($token) . '&email=' . urlencode($email);

        // 6. Envoyer l'email
        $displayName = trim($user->getPrenom() . ' ' . $user->getNom());
        Mailer::sendPasswordResetEmail($email, $displayName ?: 'Utilisateur', $resetUrl);
    }
}
