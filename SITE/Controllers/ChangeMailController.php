<?php

namespace Controllers;

use Core\Csrf;
use Core\Mailer;
use Models\User;

/**
 * Contrôleur : Changement d'adresse email
 *
 * Gère le changement d'email pour un utilisateur authentifié avec revérification
 * obligatoire de la nouvelle adresse.  Envoie des notifications aux deux adresses
 * (ancienne et nouvelle) pour des raisons de sécurité.
 *
 * @package Controllers
 */
final class ChangeMailController
{
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
        \Core\View::render('auth/change-mail', compact('errors', 'success'));
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

        $csrf            = (string)($_POST['csrf_token'] ?? '');
        $currentPassword = (string)($_POST['current_password'] ?? '');
        $newEmail        = trim((string)($_POST['new_email'] ?? ''));
        $confirmEmail    = trim((string)($_POST['new_email_confirm'] ?? ''));

        // Validation CSRF
        if (!Csrf::validate($csrf)) {
            $errors[] = 'Session expirée ou jeton CSRF invalide.';
        }

        // Vérification que les emails correspondent
        if ($newEmail !== $confirmEmail) {
            $errors[] = 'Les adresses email ne correspondent pas.';
        }

        // Validation du format email
        if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'L\'adresse email n\'est pas valide.';
        }

        if (!$errors) {
            $userId = (int)($_SESSION['user']['id'] ?? 0);
            $user   = User::findById($userId);

            // Vérification du mot de passe actuel
            if (!$user || empty($user['password']) || !password_verify($currentPassword, $user['password'])) {
                $errors[] = 'Mot de passe incorrect.';
            } else {
                $oldEmail = $user['email'];

                // Vérifier si le nouvel email est différent de l'ancien
                if (strtolower($oldEmail) === strtolower($newEmail)) {
                    $errors[] = 'La nouvelle adresse email est identique à l\'ancienne.';
                } elseif (User::emailExists($newEmail)) {
                    // Email déjà utilisé
                    $errors[] = 'Cette adresse email est déjà utilisée par un autre compte.';
                } else {
                    // Mise à jour de l'email + nouvelle vérification obligatoire
                    $token = User::updateEmailWithVerification($userId, $newEmail);

                    if ($token) {
                        // Mise à jour de la session et forcer la revérification
                        $_SESSION['user']['email'] = $newEmail;
                        $_SESSION['user']['email_verified'] = false;

                        $mailSent = Mailer::sendEmailVerification($newEmail, $user['name'], $token);
                        $this->sendEmailNotifications($oldEmail, $newEmail, $user['name']);

                        if ($mailSent) {
                            $success = 'Adresse mise à jour. Vérifiez le mail envoyé pour réactiver votre compte.';
                        } else {
                            $errors[] = 'Adresse mise à jour, mais l\'email de vérification n\'a pas pu être envoyé.';
                        }
                    } else {
                        $errors[] = 'Impossible de mettre à jour l\'adresse email pour le moment.';
                    }
                }
            }
        }

        \Core\View::render('auth/change-mail', compact('errors', 'success'));
    }

    /**
     * Envoie des emails de notification à l'ancienne et à la nouvelle adresse
     *
     * Envoie un email à :
     * - L'ancienne adresse :  notification du changement + contact support si non autorisé
     * - La nouvelle adresse : confirmation du changement + contact support si non autorisé
     *
     * @param string $oldEmail Ancienne adresse email
     * @param string $newEmail Nouvelle adresse email
     * @param string $userName Prénom de l'utilisateur
     * @return void
     */
    private function sendEmailNotifications(string $oldEmail, string $newEmail, string $userName): void
    {
        // Email à l'ancienne adresse
        $oldEmailSubject = "Modification de votre adresse email - DashMed";
        $oldEmailMessage = "Bonjour " . htmlspecialchars($userName) . ",\n\n";
        $oldEmailMessage .= "Votre adresse email associée à votre compte DashMed a été modifiée.\n\n";
        $oldEmailMessage .= "Si vous n'êtes pas à l'origine de cette modification, "
            . "veuillez contacter immédiatement notre service client pour sécuriser votre compte.\n\n";
        $oldEmailMessage .= "Cordialement,\n";
        $oldEmailMessage .= "L'équipe DashMed";

        $oldEmailHeaders = "From: noreply@dashmed.com\r\n";
        $oldEmailHeaders .= "Reply-To: support@dashmed.com\r\n";
        $oldEmailHeaders .= "Content-Type: text/plain; charset=UTF-8\r\n";

        mail($oldEmail, $oldEmailSubject, $oldEmailMessage, $oldEmailHeaders);

        // Email à la nouvelle adresse
        $newEmailSubject = "Confirmation de votre nouvelle adresse email - DashMed";
        $newEmailMessage = "Bonjour " . htmlspecialchars($userName) . ",\n\n";
        $newEmailMessage .= "Votre adresse email a été modifiée avec succès.\n\n";
        $newEmailMessage .= "Cette adresse (" . $newEmail . ") est maintenant associée à votre compte DashMed.\n\n";
        $newEmailMessage .= "Si vous n'êtes pas à l'origine de cette modification, "
            . "veuillez contacter immédiatement notre service client pour sécuriser votre compte.\n\n";
        $newEmailMessage .= "Cordialement,\n";
        $newEmailMessage .= "L'équipe DashMed";

        $newEmailHeaders = "From: noreply@dashmed.com\r\n";
        $newEmailHeaders .= "Reply-To: support@dashmed.com\r\n";
        $newEmailHeaders .= "Content-Type: text/plain; charset=UTF-8\r\n";

        mail($newEmail, $newEmailSubject, $newEmailMessage, $newEmailHeaders);
    }
}
