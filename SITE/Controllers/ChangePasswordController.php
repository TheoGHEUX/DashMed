<?php

namespace Controllers;

use Core\Csrf;
use Models\User;

/**
 * Contrôleur de changement de mot de passe.
 *
 * Permet à un utilisateur authentifié de modifier son mot de passe en vérifiant
 * l'ancien mot de passe et en appliquant les règles de complexité.
 *
 * @package Controllers
 */
final class ChangePasswordController
{
    /**
     * Affiche le formulaire de changement de mot de passe.
     *
     * Vérifie que l'utilisateur est authentifié avant d'afficher le formulaire.
     * Redirige vers la page de connexion si l'utilisateur n'est pas connecté.
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
     * Traite la soumission du formulaire de changement de mot de passe.
     *
     * Effectue les validations suivantes :
     * - Token CSRF valide
     * - Nouveau mot de passe et confirmation identiques
     * - Complexité du mot de passe (12+ caractères, maj, min, chiffre, spécial)
     * - Ancien mot de passe correct
     *
     * En cas de succès :
     * - Hash le nouveau mot de passe avec bcrypt
     * - Met à jour le mot de passe en base de données
     * - Affiche un message de confirmation
     * - L'utilisateur reste connecté (option de déconnexion automatique commentée)
     *
     * Note : Par mesure de sécurité renforcée, il est possible de déconnecter
     * automatiquement l'utilisateur après un changement de mot de passe réussi.
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
        $oldPassword  = (string)($_POST['old_password'] ??  '');
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
                . 'avec majuscules, minuscules, chiffres et un caractère spécial. ';
        }

        if (! $errors) {
            $userId = (int)($_SESSION['user']['id'] ?? 0);
            $user   = User::findById($userId);

            if (! $user || empty($user['password']) || !password_verify($oldPassword, $user['password'])) {
                $errors[] = 'Ancien mot de passe incorrect.';
            } else {
                $hash = password_hash($newPassword, PASSWORD_DEFAULT);
                if (User::updatePassword($userId, $hash)) {
                    $success = 'Votre mot de passe a été mis à jour.';
                    // Option de sécurité renforcée : déconnecter l'utilisateur après changement
                    // header('Location: /logout'); exit;
                } else {
                    $errors[] = 'Impossible de mettre à jour le mot de passe pour le moment.';
                }
            }
        }

        \Core\View::render('auth/change-password', compact('errors', 'success'));
    }
}