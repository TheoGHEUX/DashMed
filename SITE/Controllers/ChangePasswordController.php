<?php

namespace Controllers;

use Core\Csrf;
use Models\Repositories\UserRepository;

final class ChangePasswordController
{
    private UserRepository $users;

    public function __construct()
    {
        $this->users = new UserRepository();
    }

    public function showForm(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (empty($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        $errors = [];
        $success = '';

        \Core\View::render('auth/change-password', compact('errors', 'success'));
    }

    public function submit(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (empty($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        $errors = [];
        $success = '';

        $csrf         = (string)($_POST['csrf_token'] ?? '');
        $oldPassword  = (string)($_POST['old_password'] ?? '');
        $newPassword  = (string)($_POST['password'] ?? '');
        $confirm      = (string)($_POST['password_confirm'] ?? '');

        if (!Csrf::validate($csrf)) {
            $errors[] = 'Session expirée ou jeton CSRF invalide.';
        }

        if ($newPassword !== $confirm) {
            $errors[] = 'Mots de passe différents.';
        }

        if (
            strlen($newPassword) < 12 ||
            !preg_match('/[A-Z]/', $newPassword) ||
            !preg_match('/[a-z]/', $newPassword) ||
            !preg_match('/\d/', $newPassword) ||
            !preg_match('/[^A-Za-z0-9]/', $newPassword)
        ) {
            $errors[] = 'Le mot de passe doit contenir au moins 12 caractères, '
                . 'avec majuscules, minuscules, chiffres et un caractère spécial.';
        }

        if (!$errors) {

            $userId = (int)($_SESSION['user']['id'] ?? 0);
            $user = $this->users->findById($userId);

            if (!$user || !password_verify($oldPassword, $user->getPasswordHash())) {
                $errors[] = 'Ancien mot de passe incorrect.';
            } else {

                $hash = password_hash($newPassword, PASSWORD_DEFAULT);

                if ($this->users->updatePassword($userId, $hash)) {
                    $success = 'Votre mot de passe a été mis à jour.';
                } else {
                    $errors[] = 'Impossible de mettre à jour le mot de passe pour le moment.';
                }
            }
        }

        \Core\View::render('auth/change-password', compact('errors', 'success'));
    }
}