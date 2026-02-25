<?php

namespace Controllers;

use Models\Repositories\UserRepository;

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
    private UserRepository $users;

    public function __construct()
    {
        $this->users = new UserRepository();
    }

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

        require __DIR__ . '/../Views/auth/resend-verification.php';
    }
}
