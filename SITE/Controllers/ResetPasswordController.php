<?php

namespace Controllers;

use Core\Csrf;
use Core\Database;
use PDO;

/**
 * Réinitialisation du mot de passe
 *
 * Gère l'affichage du formulaire de réinitialisation (showForm) et le
 * traitement du nouveau mot de passe (submit).
 *
 * Utilise un jeton stocké dans la table password_resets et
 * protège les soumissions avec CSRF.
 *
 * Sécurité :
 * - Vérification stricte du jeton (hash SHA-256) et usage en transaction
 * - Validation côté serveur de la complexité du mot de passe
 * - Invalidation du jeton après usage (used_at)
 *
 * @package Controllers
 */
final class ResetPasswordController
{
    /**
     * Affiche le formulaire de réinitialisation.
     *
     * Valide le jeton reçu en GET (email + jeton) avant d'afficher le formulaire.
     * Affiche un message d'erreur si le jeton est invalide ou expiré.
     *
     * @return void
     */
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

        \Core\View::render('auth/reset-password', compact('errors', 'success', 'email', 'token'));
    }

    /**
     * Traite la soumission du formulaire de réinitialisation.
     *
     * Validations effectuées :
     * - Jeton CSRF
     * - Jeton de reset valide et non expiré
     * - Nouveau mot de passe conforme (12+ car., maj/min/chiffre/spécial)
     * - Confirmation correspondante
     *
     * Processus en transaction :
     * 1. Verrouille la ligne du jeton (FOR UPDATE)
     * 2. Récupère l'email associé au jeton
     * 3. Met à jour le mot de passe de l'utilisateur
     * 4. Invalide le jeton (used_at = NOW())
     * 5. Commit et redirection vers /login?reset=1
     *
     * En cas d'erreur, rollback et affichage du message d'erreur.
     *
     * @return void
     */
    public function submit(): void
    {
        $errors = [];
        $success = '';

        $csrf     = (string)($_POST['csrf_token'] ?? '');
        $token    = (string)($_POST['token'] ?? '');
        // On ne fera pas confiance à l'email posté pour la mise à jour,
        // on le garde seulement pour ré-afficher le formulaire si erreur
        $emailPosted = strtolower(trim((string)($_POST['email'] ?? '')));
        $password = (string)($_POST['password'] ?? '');
        $confirm  = (string)($_POST['password_confirm'] ?? '');

        if (!Csrf::validate($csrf)) {
            $errors[] = 'Session expirée ou jeton CSRF invalide.';
        }
        if ($token === '') {
            $errors[] = 'Lien invalide.';
        }
        if ($password !== $confirm) {
            $errors[] = 'Mots de passe différents.';
        }
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

                // 1) Retrouver l'email à partir du jeton et verrouiller la ligne (évite les courses)
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
                    \Core\View::render('auth/reset-password', [
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
                $u = $pdo->prepare(
                    'UPDATE medecin SET mdp = ?, date_derniere_maj = NOW() '
                    . 'WHERE LOWER(email) = LOWER(?)'
                );
                $u->execute([$hash, $emailFromToken]);

                if ($u->rowCount() === 0) {
                    // Aucun utilisateur correspondant -> rollback et message d'erreur
                    $pdo->rollBack();
                    error_log(sprintf(
                        '[RESET] No user row updated for email=%s (from token)',
                        $emailFromToken
                    ));
                    $errors[] = 'Une erreur technique est survenue lors de la réinitialisation. '
                        . 'Veuillez réessayer.';
                    \Core\View::render('auth/reset-password', [
                        'errors'  => $errors,
                        'success' => $success,
                        'email'   => $emailPosted,
                        'token'   => $token,
                    ]);
                    return;
                }

                // 3) Invalider le jeton (par token_hash, pour être strict)
                $t = $pdo->prepare(
                    'UPDATE password_resets SET used_at = NOW() WHERE token_hash = ? AND used_at IS NULL'
                );
                $t->execute([$tokenHash]);

                $pdo->commit();

                // 4) Rediriger vers la page de connexion avec un message de succès
                header('Location: /login?reset=1');
                exit;
            } catch (\Throwable $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                // Log interne détaillé, message neutre côté utilisateur
                error_log(sprintf('[RESET] DB error: %s in %s:%d', $e->getMessage(), $e->getFile(), $e->getLine()));
                $errors[] = 'Une erreur est survenue lors de la réinitialisation. Veuillez réessayer.';
            }
        }

        \Core\View::render('auth/reset-password', [
            'errors'  => $errors,
            'success' => $success,
            'email'   => $emailPosted,
            'token'   => $token,
        ]);
    }

    /**
     * Vérifie si un jeton de réinitialisation est valide.
     *
     * Validations :
     *  - Email et jeton non vides
     *  - Correspondance email/token_hash en base
     *  - Jeton non expiré (expires_at > NOW())
     *  - Jeton non utilisé (used_at IS NULL)
     *
     * @param string $email email de l'utilisateur
     * @param string $token
     * @return bool True si le jeton est valide, false sinon
     */
    private function isValidToken(string $email, string $token): bool
    {
        if ($email === '' || $token === '') {
            return false;
        }

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
