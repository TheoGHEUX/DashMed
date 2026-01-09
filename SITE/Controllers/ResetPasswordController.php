<?php

namespace Controllers;

use Core\Csrf;
use Core\Database;
use PDO;

/**
 * Contrôleur de réinitialisation de mot de passe.
 *
 * Gère le processus de réinitialisation via token sécurisé : validation du lien,
 * affichage du formulaire et mise à jour du mot de passe avec invalidation du token.
 *
 * @package Controllers
 */
final class ResetPasswordController
{
    /**
     * Affiche le formulaire de réinitialisation.
     *
     * Vérifie la validité du token (non expiré, non utilisé) avant d'afficher
     * le formulaire. Affiche une erreur si le lien est invalide ou expiré.
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
     * Validations :
     * - Token CSRF valide
     * - Token de réinitialisation présent
     * - Mots de passe identiques
     * - Complexité du mot de passe (12+ caractères, maj, min, chiffre, spécial)
     *
     * Processus en transaction :
     * 1. Verrouille la ligne du token (FOR UPDATE, évite race conditions)
     * 2. Récupère l'email associé au token
     * 3. Met à jour le mot de passe de l'utilisateur
     * 4. Invalide le token (used_at = NOW())
     * 5. Redirige vers /login avec message de succès
     *
     * Le token est hashé en SHA-256 côté serveur.  L'email utilisé pour la mise
     * à jour provient du token (pas du POST) pour éviter toute manipulation.
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

                // 1) Retrouver l'email à partir du token et verrouiller la ligne (évite les courses)
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
                        // on garde ce qui était dans le formulaire pour ne pas "perdre" l'utilisateur
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
                    $errors[] = 'Une erreur technique est survenue lors de la réinitialisation.  '
                        . 'Veuillez réessayer.';
                    \Core\View:: render('auth/reset-password', [
                        'errors'  => $errors,
                        'success' => $success,
                        'email'   => $emailPosted,
                        'token'   => $token,
                    ]);
                    return;
                }

                // 3) Invalider le token (par token_hash, pour être strict)
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
     * Vérifie la validité d'un token de réinitialisation.
     *
     * Un token est valide s'il existe en base, n'est pas expiré et n'a pas
     * encore été utilisé.  Le token est hashé en SHA-256 avant vérification.
     *
     * @param string $email Adresse email associée au token
     * @param string $token Token de réinitialisation (64 hex chars)
     * @return bool True si le token est valide, false sinon
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