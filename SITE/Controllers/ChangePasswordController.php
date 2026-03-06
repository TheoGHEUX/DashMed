<?php

namespace Controllers;

use Core\Csrf;
use Core\Interfaces\ChangePasswordUseCaseInterface;
use Core\View;

final class ChangePasswordController
{
    private ChangePasswordUseCaseInterface $useCase;

    // Injection via le constructeur
    public function __construct(ChangePasswordUseCaseInterface $useCase)
    {
        $this->useCase = $useCase;
    }

    public function showForm(): void
    {
        $this->ensureAuth();
        $errors = [];
        $success = '';
        View::render('auth/change-password', compact('errors', 'success'));
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
            // Appel du Use Case
            $userId = (int) $_SESSION['user']['id'];
            $old = $_POST['old_password'] ?? '';
            $new = $_POST['password'] ?? '';
            $confirm = $_POST['password_confirm'] ?? '';

            $result = $this->useCase->execute($userId, $old, $new, $confirm);

            if ($result['success']) {
                $success = $result['message'];
            } else {
                $errors = $result['errors'];
            }
        }

        View::render('auth/change-password', compact('errors', 'success'));
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