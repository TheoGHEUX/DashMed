<?php

namespace Controllers;

use Models\User;

/**
 * Vérification d'email
 *
 * Gère l'activation du compte via jeton de vérification et le renvoi d'un
 * nouvel email en cas d'expiration.
 *
 * @package Controllers
 */
final class VerifyEmailController
{
    /**
     * Vérifie le jeton d'email et active le compte.
     *
     * Processus :
     * 1. Récupère le jeton depuis GET
     * 2. Recherche l'utilisateur associé au jeton
     * 3. Vérifie l'expiration du jeton (24 heures)
     * 4. Active le compte si valide (email_verified = 1, suppression du jeton)
     *
     * Messages possibles :
     * - Jeton manquant/invalide → erreur
     * - Email déjà vérifié → succès (message informatif)
     * - Jeton expiré → erreur avec suggestion de renvoyer le lien
     * - Vérification réussie → succès avec redirection vers login
     *
     * @return void
     */
    public function verify(): void
    {
        $token = trim((string)($_GET['token'] ?? ''));
        $success = '';
        $errors = [];

        if (empty($token)) {
            $errors[] = 'Token de vérification manquant.';
        } else {
            // Recherche de l'utilisateur par jeton
            $user = User::findByVerificationToken($token);

            if (!$user) {
                $errors[] = 'Token de vérification invalide ou expiré.';
            } elseif ($user['email_verified']) {
                $success = 'Votre adresse email est déjà vérifiée. Vous pouvez vous connecter.';
            } else {
                // Vérification de l'expiration
                $now = new \DateTime();
                $expires = new \DateTime($user['email_verification_expires']);

                if ($now > $expires) {
                    $errors[] = 'Le lien de vérification a expiré. Veuillez demander un nouveau lien.';
                } else {
                    // Validation du jeton et activation du compte
                    if (User::verifyEmailToken($token)) {
                        $success = 'Votre adresse email a été vérifiée avec succès ! '
                            . 'Vous pouvez maintenant vous connecter.';
                    } else {
                        $errors[] = 'Une erreur est survenue lors de la vérification. Veuillez réessayer.';
                    }
                }
            }
        }

        require __DIR__ . '/../Views/auth/verify-email.php';
    }

    /**
     * Renvoie un email de vérification.
     *
     * Processus si l'email existe et n'est pas vérifié :
     * 1. Génère un nouveau jeton (64 hex chars, valide 24h)
     * 2. Met à jour la base (email_verification_token, expires)
     * 3. Envoie l'email via Mailer::sendEmailVerification()
     *
     * @return void
     */
    public function resend(): void
    {
        $email = trim((string)($_POST['email'] ?? ''));
        $errors = [];
        $success = '';

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Adresse email invalide.';
        } else {
            $user = User::findByEmail($email);

            if (!$user) {
                // Ne pas révéler si l'email existe ou non (sécurité)
                $success = 'Si cette adresse email est enregistrée et non vérifiée, '
                    . 'un nouvel email de vérification a été envoyé.';
            } elseif ($user['email_verified']) {
                $errors[] = 'Cette adresse email est déjà vérifiée.';
            } else {
                // Génération d'un nouveau jeton
                $token = User::generateEmailVerificationToken($email);

                if ($token) {
                    $mailSent = \Core\Mailer::sendEmailVerification($email, $user['name'], $token);

                    if ($mailSent) {
                        $success = 'Un nouvel email de vérification a été envoyé à votre adresse.';
                    } else {
                        $errors[] = 'Erreur lors de l\'envoi de l\'email. Veuillez réessayer plus tard.';
                    }
                } else {
                    $errors[] = 'Erreur lors de la génération du token. Veuillez réessayer.';
                }
            }
        }

        require __DIR__ . '/../Views/auth/resend-verification.php';
    }
}
