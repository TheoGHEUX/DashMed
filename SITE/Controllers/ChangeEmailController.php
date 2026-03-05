<?php

namespace Controllers;

use Core\Interfaces\UserRepositoryInterface; // <-- Interface !
use Domain\UseCases\Auth\ChangeEmailUseCase; // <-- Use Case !
use Core\Csrf;
use Core\View;

final class ChangeEmailController
{
    private UserRepositoryInterface $userRepo;

    // Injection de dépendance via le constructeur
    public function __construct(UserRepositoryInterface $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    public function showForm(): void
    {
        $this->ensureAuth();
        $errors = [];
        $success = '';
        View::render('auth/change-email', compact('errors', 'success'));
    }

    public function submit(): void
    {
        $this->ensureAuth();

        $errors = [];
        $success = '';
        $csrf = $_POST['csrf_token'] ?? '';

        if (!Csrf::validate($csrf)) {
            $errors[] = 'Session expirée.';
        } else {
            // Préparation des données pour le Use Case
            $data = $_POST;
            $data['userId'] = $_SESSION['user']['id']; // On ajoute l'ID de session

            // Appel du Use Case
            $useCase = new ChangeEmailUseCase($this->userRepo);
            $result = $useCase->execute($data);

            if ($result['success']) {
                $success = $result['message'];
                // Mise à jour de la session (Responsabilité du contrôleur HTTP)
                $_SESSION['user']['email'] = $result['newEmail'];
                $_SESSION['user']['email_verified'] = false;
            } else {
                $errors = $result['errors'];
            }
        }

        View::render('auth/change-email', compact('errors', 'success'));
    }

    private function ensureAuth(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        if (empty($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }
    }
}