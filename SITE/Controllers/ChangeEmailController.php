<?php

namespace Controllers;

use Core\Csrf;
use Core\Mailer;
use Core\View;
use Models\Repositories\UserRepository;

final class ChangeEmailController
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

        View::render('auth/change-email', compact('errors', 'success'));
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

        if (!Csrf::validate($csrf)) {
            $errors[] = 'Session expirée ou jeton CSRF invalide.';
        }

        if ($newEmail !== $confirmEmail) {
            $errors[] = 'Les adresses email ne correspondent pas.';
        }

        if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Adresse email invalide.';
        }

        if (!$errors) {

            $userId = (int)($_SESSION['user']['id'] ?? 0);
            $user = $this->users->findById($userId);

            if (!$user) {
                $errors[] = 'Utilisateur introuvable.';
            } elseif (!password_verify($currentPassword, $user->getPasswordHash())) {
                $errors[] = 'Mot de passe incorrect.';
            } else {

                $oldEmail = $user->getEmail();

                if (strtolower($oldEmail) === strtolower($newEmail)) {
                    $errors[] = 'La nouvelle adresse email est identique à l’ancienne.';
                }

                elseif ($this->users->emailExists($newEmail)) {
                    $errors[] = 'Cette adresse email est déjà utilisée.';
                }

                else {

                    $token = bin2hex(random_bytes(32));
                    $expires = (new \DateTime('+24 hours'))->format('Y-m-d H:i:s');

                    // 1️⃣ changer l’email
                    $updated = $this->users->updateEmail($userId, $newEmail);

                    if ($updated) {

                        // 2️⃣ créer un token de vérification
                        $this->users->setVerificationToken($newEmail, $token, $expires);

                        $_SESSION['user']['email'] = $newEmail;
                        $_SESSION['user']['email_verified'] = false;

                        $mailSent = Mailer::sendEmailVerification(
                            $newEmail,
                            $user->getPrenom(),
                            $token
                        );

                        $this->sendEmailNotifications(
                            $oldEmail,
                            $newEmail,
                            $user->getPrenom()
                        );

                        if ($mailSent) {
                            $success = 'Adresse mise à jour. Vérifiez votre email pour réactiver votre compte.';
                        } else {
                            $errors[] = 'Adresse mise à jour mais email non envoyé.';
                        }

                    } else {
                        $errors[] = 'Impossible de modifier l’adresse email.';
                    }
                }
            }
        }

        View::render('auth/change-email', compact('errors', 'success'));
    }

    private function sendEmailNotifications(string $oldEmail, string $newEmail, string $userName): void
    {
        $subjectOld = "Modification de votre adresse email - DashMed";
        $messageOld = "Bonjour $userName,

        Votre adresse email DashMed a été modifiée.

        Si vous n'êtes pas à l'origine de cette action, contactez immédiatement le support.

        L'équipe DashMed";

        mail($oldEmail, $subjectOld, $messageOld);

        $subjectNew = "Confirmation de votre nouvelle adresse email - DashMed";
        $messageNew = "Bonjour $userName,

        Votre adresse email a été modifiée avec succès.

        Nouvelle adresse : $newEmail

        Si ce n'est pas vous, contactez le support.

        L'équipe DashMed";

        mail($newEmail, $subjectNew, $messageNew);
    }
}