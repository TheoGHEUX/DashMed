<?php

namespace Controllers;

use Core\Csrf;
use Core\Database;
use Core\Mailer;
use Models\User;
use PDO;

/**
 * Contrôleur : Mot de passe oublié
 *
 * Gère l'affichage du formulaire de demande de réinitialisation de mot de passe
 * et l'envoi du lien de réinitialisation par email. La réponse côté UI est
 * neutre pour ne pas révéler l'existence d'un compte.
 *
 * Méthodes :
 *  - showForm(): affiche la vue 'auth/forgotten-password'
 *  - submit():   traite la demande, crée un token et envoie l'email (si le compte existe)
 *
 * @package Controllers
 */
final class ForgottenPasswordController
{
    public function showForm(): void
    {
        $errors = $_SESSION['errors'] ?? null;
        $success = $_SESSION['success'] ?? null;
        $old = $_SESSION['old'] ?? null;

        unset($_SESSION['errors'], $_SESSION['success'], $_SESSION['old']);

        \Core\View::render('auth/forgotten-password', [
            'errors' => $errors,
            'success' => $success,
            'old' => $old
        ]);
    }

    public function submit(): void
    {
        $errors = [];
        $success = '';
        // Simple rate-limiting per session to avoid abuse of password reset endpoint
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $maxAttempts = 5;
        $windowSeconds = 3600; // 1 hour
        $now = time();
        $fpAttempts = $_SESSION['forgot_password_attempts'] ?? [];
        $fpAttempts = array_filter($fpAttempts, function ($ts) use ($now, $windowSeconds) {
            return ($now - $ts) <= $windowSeconds;
        });
        if (count($fpAttempts) >= $maxAttempts) {
            // Keep UI neutral, set success message and redirect
            $success = "Si un compte existe à cette adresse mail, "
                . "un lien de réinitialisation a été envoyé.\n"
                . "Veuillez attendre avant de refaire une demande.";
            $_SESSION['success'] = $success;
            header('Location: /forgotten-password');
            exit;
        }
        $old = [
            'email' => trim((string)($_POST['email'] ?? '')),
        ];
        $csrf = (string)($_POST['csrf_token'] ?? '');

        if (!Csrf::validate($csrf)) {
            $errors[] = 'Session expirée ou jeton CSRF invalide.';
        }

        if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Adresse email invalide.';
        }

        if (!$errors) {
            try {
                // Vérifie si l'email est inscrit
                $user = User::findByEmail($old['email']);
                if ($user) {
                    $pdo = Database::getConnection();

                    // Nettoyage des anciens tokens pour le mail associer au mdp réunitialiser
                    $del = $pdo->prepare('DELETE FROM password_resets WHERE email = ? OR expires_at < NOW()');
                    $del->execute([$old['email']]);

                    // Génère un nouveau token
                    $token = bin2hex(random_bytes(32));
                    $tokenHash = hash('sha256', $token);
                    $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 60 minutes

                    $ins = $pdo->prepare(
                        'INSERT INTO password_resets (email, token_hash, expires_at) VALUES (?, ?, ?)'
                    );
                    $ins->execute([$old['email'], $tokenHash, $expiresAt]);

                    // Construit l'URL reset
                    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                        ? 'https' : 'http';
                    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                    $resetUrl = $scheme . '://' . $host . '/reset-password?token='
                        . urlencode($token) . '&email=' . urlencode($old['email']);

                    // Nom d’affichage
                    $displayName = trim(($user['name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
                    Mailer::sendPasswordResetEmail($old['email'], $displayName ?: 'Utilisateur', $resetUrl);
                } else {
                    // En prod on reste neutre côté UI, mais on log pour faciliter le debug
                    error_log(sprintf(
                        '[FORGOT] Aucun utilisateur trouvé pour %s (message neutre renvoyé)',
                        $old['email']
                    ));
                }

                // Réponse neutre (utilise une vraie nouvelle ligne avec "\n")
                $success = "Si un compte existe à cette adresse mail, "
                    . "un lien de réinitialisation a été envoyé.\n"
                    . "N'oubliez pas de vérifier votre courrier indésirable.";
                $old = ['email' => ''];
            } catch (\Throwable $e) {
                error_log(sprintf(
                    '[FORGOT] %s in %s:%d',
                    $e->getMessage(),
                    $e->getFile(),
                    $e->getLine()
                ));
                $success = "Si un compte existe à cette adresse mail, "
                    . "un lien de réinitialisation a été envoyé.\n"
                    . "N'oubliez pas de vérifier votre courrier indésirable.";
                $old = ['email' => ''];
            }
        } else {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $old;
        }

        // record this forgot-password request timestamp
        $fpAttempts[] = $now;
        $_SESSION['forgot_password_attempts'] = $fpAttempts;
        $_SESSION['success'] = $success;
        header('Location: /forgotten-password');
        exit;
    }
}
