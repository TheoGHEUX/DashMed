<?php

namespace Controllers;

use Models\Repositories\UserRepository;

final class VerifyEmailController
{
    private UserRepository $users;

    public function __construct()
    {
        $this->users = new UserRepository();
    }

    public function verify(): void
    {
        $token = trim((string)($_GET['token'] ?? ''));
        $success = '';
        $errors = [];

        if (empty($token)) {
            $errors[] = 'Token de vérification manquant.';
        } else {
            $user = $this->users->findByVerificationToken($token);

            if (!$user) {
                $errors[] = 'Token de vérification invalide ou expiré.';
            } elseif ($user->isEmailVerified()) {
                $success = 'Votre adresse email est déjà vérifiée. Vous pouvez vous connecter.';
            } else {

                $now = new \DateTime();
                $expires = new \DateTime($user->getVerificationExpires() ?? 'now');

                if ($now > $expires) {
                    $errors[] = 'Le lien de vérification a expiré. Veuillez demander un nouveau lien.';
                } else {
                    if ($this->users->verifyEmailToken($token)) {
                        $success = 'Votre adresse email a été vérifiée avec succès ! Vous pouvez maintenant vous connecter.';
                    } else {
                        $errors[] = 'Une erreur est survenue lors de la vérification.';
                    }
                }
            }
        }

        \Core\View::render('auth/verify-email', compact('errors', 'success'));
    }

    public function resend(): void
    {
        $email = trim((string)($_POST['email'] ?? ''));
        $errors = [];
        $success = '';

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Adresse email invalide.';
        } else {

            $user = $this->users->findByEmail($email);

            if (!$user) {
                $success = 'Si cette adresse email est enregistrée et non vérifiée, un email a été envoyé.';
            } elseif ($user->isEmailVerified()) {
                $errors[] = 'Cette adresse email est déjà vérifiée.';
            } else {

                $token = bin2hex(random_bytes(32));
                $expires = (new \DateTime('+24 hours'))->format('Y-m-d H:i:s');

                $ok = $this->users->setVerificationToken($email, $token, $expires);

                if ($ok) {
                    $mailSent = \Core\Mailer::sendEmailVerification($email, $user->getPrenom(), $token);

                    if ($mailSent) {
                        $success = 'Un nouvel email de vérification a été envoyé.';
                    } else {
                        $errors[] = 'Erreur lors de l\'envoi de l\'email.';
                    }
                } else {
                    $errors[] = 'Impossible de générer un nouveau lien.';
                }
            }
        }

        \Core\View::render('auth/resend-verification', compact('errors', 'success'));
    }
}