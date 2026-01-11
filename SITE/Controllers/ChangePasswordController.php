<?php

namespace Controllers;

use Core\Csrf;
use Models\User;

/**
 * Changement de mot de passe
 *
 * Gère l'affichage et le traitement du formulaire de modification de mot de
 * passe pour un utilisateur connecté.
 *
 * Vérifie l'ancien mot de passe et applique les règles de complexité.
 *
 * @package Controllers
 */
final class ChangePasswordController
{
    /**
     * Affiche le formulaire de changement de mot de passe.
     *
     * Vérifie l'authentification avant d'afficher la vue.
     * Redirige vers /login si l'utilisateur n'est pas connecté.
     *
     * @return void
     */
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

    /**
     * Traite le changement de mot de passe.
     *
     * Validations effectuées :
     * - Token CSRF
     * - Ancien mot de passe correct
     * - Nouveau mot de passe conforme (12+ caractères, maj/min/chiffre/char spécial)
     * - Confirmation correspondante
     *
     * En cas de succès, le mot de passe est mis à jour en base (hash bcrypt)
     * et un message de succès est affiché.  L'utilisateur reste connecté.
     *
     * @return void
     */
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
            $user   = User::findById($userId);

            if (!$user || empty($user['password']) || !password_verify($oldPassword, $user['password'])) {
                $errors[] = 'Ancien mot de passe incorrect.';
            } else {
                $hash = password_hash($newPassword, PASSWORD_DEFAULT);
                if (User::updatePassword($userId, $hash)) {
                    $success = 'Votre mot de passe a été mis à jour.';
                    // on peut vouloir déconnecter l’utilisateur sur changement de mdp pour sécurité
                    // header('Location: /logout'); exit;
                } else {
                    $errors[] = 'Impossible de mettre à jour le mot de passe pour le moment.';
                }
            }
        }

        \Core\View::render('auth/change-password', compact('errors', 'success'));
    }
}
