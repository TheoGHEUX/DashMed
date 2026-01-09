<?php

namespace Controllers;

use Core\Csrf;
use Core\Mailer;
use Models\User;

/**
 * Contrôleur de changement d'adresse email.
 *
 * Permet à un utilisateur authentifié de modifier son adresse email.
 * Le changement force une nouvelle vérification d'email pour des raisons de sécurité,
 * et envoie des notifications à l'ancienne et à la nouvelle adresse.
 *
 * @package Controllers
 */
final class ChangeMailController
{
    /**
     * Affiche le formulaire de changement d'adresse email.
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
        \Core\View::render('auth/change-mail', compact('errors', 'success'));
    }

    /**
     * Traite la soumission du formulaire de changement d'email.
     *
     * Effectue les validations suivantes :
     * - Token CSRF valide
     * - Les deux emails saisis correspondent
     * - Format d'email valide
     * - Mot de passe actuel correct
     * - Nouvel email différent de l'ancien
     * - Nouvel email libre et non utilisé par un autre compte
     *
     * En cas de succès :
     * - Met à jour l'email en base de données
     * - Marque l'email comme non vérifié (email_verified=0)
     * - Génère un nouveau token de vérification (24h)
     * - Envoie un email de vérification à la nouvelle adresse
     * - Envoie des notifications de sécurité aux deux adresses
     * - Met à jour la session (email + email_verified=false)
     *
     * L'utilisateur devra vérifier sa nouvelle adresse avant de pouvoir se
     * reconnecter.
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

        $csrf            = (string)($_POST['csrf_token'] ?? '');
        $currentPassword = (string)($_POST['current_password'] ?? '');
        $newEmail        = trim((string)($_POST['new_email'] ?? ''));
        $confirmEmail    = trim((string)($_POST['new_email_confirm'] ?? ''));

        // Validation CSRF
        if (! Csrf::validate($csrf)) {
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
                            $success = 'Adresse mise à jour.  Vérifiez le mail envoyé pour réactiver votre compte.';
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
     * Envoie des notifications par email aux anciennes et nouvelles adresses.
     *
     * Mesure de sécurité :
     * - L'ancienne adresse reçoit une alerte de modification pour détecter
     *   un changement non autorisé
     * - La nouvelle adresse reçoit une confirmation de l'association au compte
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
        $oldEmailMessage = "Bonjour " .  htmlspecialchars($userName) . ",\n\n";
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

        $newEmailHeaders = "From:  noreply@dashmed.com\r\n";
        $newEmailHeaders .= "Reply-To: support@dashmed.com\r\n";
        $newEmailHeaders .= "Content-Type: text/plain; charset=UTF-8\r\n";

        mail($newEmail, $newEmailSubject, $newEmailMessage, $newEmailHeaders);
    }
}