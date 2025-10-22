<?php
namespace Controllers;

use Core\Csrf;
use Core\Database;
use PDO;

final class ResetPasswordController
{
    public function showForm(): void
    {
        $errors = [];
        $success = '';

        // Normaliser l'email
        $email = strtolower(trim((string)($_GET['email'] ?? '')));
        $token = (string)($_GET['token'] ?? '');

        if (!$this->isValidToken($email, $token)) {
            $errors[] = 'Lien de réinitialisation invalide ou expiré.';
        }

    \View::render('auth/reset-password', compact('errors', 'success', 'email', 'token'));
    }

    public function submit(): void
    {
        $errors = [];
        $success = '';

        $csrf     = (string)($_POST['csrf_token'] ?? '');
        $token    = (string)($_POST['token'] ?? '');
        // On ne fera pas confiance à l'email posté pour la mise à jour, on le garde seulement pour ré-afficher le formulaire si erreur
        $emailPosted = strtolower(trim((string)($_POST['email'] ?? '')));
        $password = (string)($_POST['password'] ?? '');
        $confirm  = (string)($_POST['password_confirm'] ?? '');

        if (!Csrf::validate($csrf))                            $errors[] = 'Session expirée ou jeton CSRF invalide.';
        if ($token === '')                                     $errors[] = 'Lien invalide.';
        if ($password !== $confirm)                            $errors[] = 'Mots de passe différents.';
        if (
            strlen($password) < 12 ||
            !preg_match('/[A-Z]/', $password) ||
            !preg_match('/[a-z]/', $password) ||
            !preg_match('/\d/', $password) ||
            !preg_match('/[^A-Za-z0-9]/', $password)
        ) {
            $errors[] = 'Mot de passe trop faible (12+ car., maj/min/chiffre/spécial).';
        }

        if (!$errors) {
            $pdo = Database::getConnection();
            $tokenHash = hash('sha256', $token);

            try {
                $pdo->beginTransaction();

                // 1) Retrouver l'email à partir du token, verrouiller la ligne du reset (évite les courses)
                $sel = $pdo->prepare('
                    SELECT email
                    FROM password_resets
                    WHERE token_hash = ?
                      AND expires_at > NOW()
                      AND used_at IS NULL
                    LIMIT 1
                    FOR UPDATE
                ');
                $sel->execute([$tokenHash]);
                $row = $sel->fetch(PDO::FETCH_ASSOC);

                if (!$row || empty($row['email'])) {
                    $pdo->rollBack();
                    $errors[] = 'Lien de réinitialisation invalide ou expiré.';
                    \View::render('auth/reset-password', [
                        'errors'  => $errors,
                        'success' => $success,
                        // on garde ce qui était dans le formulaire pour ne pas "perdre" l’utilisateur
                        'email'   => $emailPosted,
                        'token'   => $token,
                    ]);
                    return;
                }

                $emailFromToken = strtolower(trim((string)$row['email']));

                // 2) Mettre à jour le mot de passe de l'utilisateur correspondant à l'email récupéré
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $u = $pdo->prepare('UPDATE users SET password = ?, updated_at = NOW() WHERE LOWER(email) = LOWER(?)');
                $u->execute([$hash, $emailFromToken]);

                if ($u->rowCount() === 0) {
                    // Aucun utilisateur correspondant -> rollback et message d'erreur
                    $pdo->rollBack();
                    error_log(sprintf('[RESET] No user row updated for email=%s (from token)', $emailFromToken));
                    $errors[] = 'Une erreur technique est survenue lors de la réinitialisation. Veuillez réessayer.';
                    \View::render('auth/reset-password', [
                        'errors'  => $errors,
                        'success' => $success,
                        'email'   => $emailPosted,
                        'token'   => $token,
                    ]);
                    return;
                }

                // 3) Invalider le token (par token_hash, pour être strict)
                $t = $pdo->prepare('UPDATE password_resets SET used_at = NOW() WHERE token_hash = ? AND used_at IS NULL');
                $t->execute([$tokenHash]);

                $pdo->commit();

                // 4) Rediriger vers la page de connexion avec un message de succès
                header('Location: /login?reset=1');
                exit;
            } catch (\Throwable $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                error_log(sprintf('[RESET] %s in %s:%d', $e->getMessage(), $e->getFile(), $e->getLine()));
                $errors[] = 'Erreur base.';
            }
        }

        \View::render('auth/reset-password', [
            'errors'  => $errors,
            'success' => $success,
            'email'   => $emailPosted, 
            'token'   => $token,
        ]);
    }

    private function isValidToken(string $email, string $token): bool
    {
        if ($email === '' || $token === '') return false;

        $pdo = Database::getConnection();
        $tokenHash = hash('sha256', $token);

        $st = $pdo->prepare('
            SELECT 1
            FROM password_resets
            WHERE LOWER(email) = LOWER(?)
              AND token_hash = ?
              AND expires_at > NOW()
              AND used_at IS NULL
            LIMIT 1
        ');
        $st->execute([$email, $tokenHash]);

        return (bool) $st->fetchColumn();
    }
}
