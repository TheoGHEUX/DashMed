<?php

namespace Controllers;

use Core\Csrf;
use Core\Interfaces\PasswordResetRepositoryInterface;
use Core\Interfaces\UserRepositoryInterface;
use Core\View;
use Domain\UseCases\Auth\RequestPasswordResetUseCase;

final class ForgottenPasswordController
{
    private UserRepositoryInterface $userRepo;
    private PasswordResetRepositoryInterface $pwdRepo;

    public function __construct(UserRepositoryInterface $userRepo, PasswordResetRepositoryInterface $pwdRepo)
    {
        $this->userRepo = $userRepo;
        $this->pwdRepo = $pwdRepo;
    }

    public function showForm(): void
    {
        // Gestion des messages Flash (Session)
        $errors = $_SESSION['errors'] ?? null;
        $success = $_SESSION['success'] ?? null;
        $old = $_SESSION['old'] ?? null;

        // Nettoyage immédiat (Flash)
        unset($_SESSION['errors'], $_SESSION['success'], $_SESSION['old']);

        View::render('auth/forgotten-password', compact('errors', 'success', 'old'));
    }

    public function submit(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();

        // 1. Rate Limiting (Protection anti-abus)
        if ($this->isRateLimited()) {
            $_SESSION['success'] = "Si un compte existe, un lien a été envoyé. Veuillez patienter.";
            header('Location: /forgotten-password');
            exit;
        }

        $email = trim($_POST['email'] ?? '');
        $csrf = $_POST['csrf_token'] ?? '';
        $errors = [];

        // 2. Validation de base
        if (!Csrf::validate($csrf)) {
            $errors[] = 'Session expirée.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Adresse email invalide.';
        }

        // 3. Exécution
        if (empty($errors)) {
            try {
                // Instanciation du Use Case avec les dépendances injectées
                $useCase = new RequestPasswordResetUseCase($this->userRepo, $this->pwdRepo);
                $useCase->execute($email);

                // Succès (Message neutre pour la sécurité)
                $_SESSION['success'] = "Si un compte existe à cette adresse, un lien de réinitialisation a été envoyé.";

                // On efface l'email du formulaire
                $_SESSION['old'] = [];

            } catch (\Exception $e) {
                // Log l'erreur mais ne l'affiche pas à l'utilisateur
                error_log("Reset Password Error: " . $e->getMessage());
                $_SESSION['success'] = "Si un compte existe à cette adresse, un lien de réinitialisation a été envoyé.";
            }
        } else {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = ['email' => $email];
        }

        header('Location: /forgotten-password');
        exit;
    }

    /**
     * Vérifie si l'utilisateur a dépassé la limite de tentatives.
     */
    private function isRateLimited(): bool
    {
        $maxAttempts = 5;
        $window = 3600; // 1 heure
        $now = time();

        $attempts = $_SESSION['forgot_password_attempts'] ?? [];

        // Filtre les tentatives de moins d'une heure
        $attempts = array_filter($attempts, fn($ts) => ($now - $ts) <= $window);

        // Ajoute la tentative actuelle
        $attempts[] = $now;
        $_SESSION['forgot_password_attempts'] = $attempts;

        return count($attempts) > $maxAttempts;
    }
}