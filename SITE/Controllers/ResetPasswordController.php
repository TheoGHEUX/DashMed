<?php

namespace Controllers;

use Core\Interfaces\UserRepositoryInterface;
use Models\Repositories\PasswordResetRepository;
use Domain\UseCases\Auth\ResetPasswordUseCase;
use Core\Csrf;
use Core\View;

final class ResetPasswordController
{
    private UserRepositoryInterface $userRepo;
    private PasswordResetRepository $pwdRepo;

    // Injection automatique via le Conteneur
    public function __construct(UserRepositoryInterface $userRepo, PasswordResetRepository $pwdRepo)
    {
        $this->userRepo = $userRepo;
        $this->pwdRepo = $pwdRepo;
    }

    public function showForm(): void
    {
        $errors = [];
        $success = '';

        $token = $_GET['token'] ?? '';
        $email = $_GET['email'] ?? '';

        // Petite vérification visuelle (optionnelle)
        // On doit hasher le token pour vérifier s'il existe en base
        $tokenHash = hash('sha256', $token);
        if (!$this->pwdRepo->findEmailByToken($tokenHash)) {
            $errors[] = 'Lien invalide ou expiré.';
        }

        View::render('auth/reset-password', compact('errors', 'success', 'token', 'email'));
    }

    public function submit(): void
    {
        $errors = [];
        $csrf = $_POST['csrf_token'] ?? '';
        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['password_confirm'] ?? '';

        if (!Csrf::validate($csrf)) {
            $errors[] = 'Session expirée.';
        }

        if (empty($errors)) {
            // Instanciation du Use Case
            $useCase = new ResetPasswordUseCase($this->userRepo, $this->pwdRepo);

            // Exécution
            $result = $useCase->execute($token, $password, $confirm);

            if ($result['success']) {
                // Redirection vers le login avec un flag pour afficher un message
                header('Location: /login?reset=1');
                exit;
            } else {
                $errors[] = $result['error'];
            }
        }

        // Si erreur, on réaffiche le formulaire
        $email = $_POST['email'] ?? '';
        $success = '';
        View::render('auth/reset-password', compact('errors', 'success', 'token', 'email'));
    }
}