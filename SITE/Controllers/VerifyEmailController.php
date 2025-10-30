<?php
namespace Controllers;

use Models\User;

final class VerifyEmailController
{
    /**
     * Affiche la page de vérification d'email
     */
    public function verify(): void
    {
        $token = trim((string)($_GET['token'] ?? ''));
        $success = '';
        $errors = [];

        if (empty($token)) {
            $errors[] = 'Token de vérification manquant.';
        } else {
            // Recherche de l'utilisateur par token
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
                    // Validation du token et activation du compte
                    if (User::verifyEmailToken($token)) {
                        $success = 'Votre adresse email a été vérifiée avec succès ! Vous pouvez maintenant vous connecter.';
                    } else {
                        $errors[] = 'Une erreur est survenue lors de la vérification. Veuillez réessayer.';
                    }
                }
            }
        }

        require __DIR__ . '/../Views/auth/verify-email.php';
    }

    /**
     * Renvoie un email de vérification
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
                $success = 'Si cette adresse email est enregistrée et non vérifiée, un nouvel email de vérification a été envoyé.';
            } elseif ($user['email_verified']) {
                $errors[] = 'Cette adresse email est déjà vérifiée.';
            } else {
                // Génération d'un nouveau token
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
