<?php
namespace Controllers;

use Core\Csrf;
use Core\Database;
use Core\Mailer;
use Models\User;
use PDO;

/**
 * Contrôleur pour la fonctionnalité "mot de passe oublié" : affichage du formulaire
 * et traitement de la demande de réinitialisation (envoi du lien par email).
 */
final class ForgottenPasswordController
{
    /**
     * Affiche le formulaire de demande de réinitialisation et transmet
     * les messages d'erreur/succès conservés en session.
     *
     * @return void
     */
    public function showForm(): void
    {
        $errors = $_SESSION['errors'] ?? null;
        $success = $_SESSION['success'] ?? null;
        $old = $_SESSION['old'] ?? null;

        unset($_SESSION['errors'], $_SESSION['success'], $_SESSION['old']);

        \View::render('auth/forgotten-password', [
            'errors' => $errors,
            'success' => $success,
            'old' => $old
        ]);
    }

    /**
     * Traite la soumission du formulaire : validation, génération de token,
     * enregistrement en base et envoi (ne révèle jamais si l'email existe).
     *
     * @return void
     */
    public function submit(): void
    {
        $errors = [];
        $success = '';
        // Limitation simple par session pour éviter les abus
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $maxAttempts = 5;
        $windowSeconds = 3600; // 1 heure
        $now = time();
        $fpAttempts = $_SESSION['forgot_password_attempts'] ?? [];
        $fpAttempts = array_filter($fpAttempts, function ($ts) use ($now, $windowSeconds) {
            return ($now - $ts) <= $windowSeconds;
        });
        if (count($fpAttempts) >= $maxAttempts) {
            // Réponse neutre côté UI
            $success = "Si un compte existe à cette adresse mail, un lien de réinitialisation a été envoyé.\nVeuillez attendre avant de refaire une demande.";
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

                    // Supprime les anciens tokens pour cet email ou expirés
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

                    // Construit l'URL de réinitialisation
                    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                    $resetUrl = $scheme . '://' . $host . '/reset-password?token=' . urlencode($token) . '&email=' . urlencode($old['email']);

                    // Nom d’affichage
                    $displayName = trim(($user['name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
                    Mailer::sendPasswordResetEmail($old['email'], $displayName ?: 'Utilisateur', $resetUrl);
                } else {
                    // En production, on reste neutre côté UI, mais on log pour faciliter le debug
                    error_log(sprintf('[FORGOT] Aucun utilisateur trouvé pour %s (message neutre renvoyé)', $old['email']));
                }

                // Réponse neutre
                $success = "Si un compte existe à cette adresse mail, un lien de réinitialisation a été envoyé.\nN'oubliez pas de vérifier votre courrier indésirable.";
                $old = ['email' => ''];
            } catch (\Throwable $e) {
                error_log(sprintf('[FORGOT] %s in %s:%d', $e->getMessage(), $e->getFile(), $e->getLine()));
                $success = "Si un compte existe à cette adresse mail, un lien de réinitialisation a été envoyé.\nN'oubliez pas de vérifier votre courrier indésirable.";
                $old = ['email' => ''];
            }
        } else {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $old;
        }

        // Enregistrer l'horodatage de la demande pour la limitation
        $fpAttempts[] = $now;
        $_SESSION['forgot_password_attempts'] = $fpAttempts;
        $_SESSION['success'] = $success;
        header('Location: /forgotten-password');
        exit;
    }
}