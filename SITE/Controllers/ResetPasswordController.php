<?php

namespace Controllers;

use Core\Csrf;
use Core\Database;
use Core\View;
use Models\Repositories\PasswordResetRepository;
use Models\Repositories\UserRepository;

final class ResetPasswordController
{
    private PasswordResetRepository $resetRepo;
    private UserRepository $userRepo;

    public function __construct()
    {
        $this->resetRepo = new PasswordResetRepository();
        $this->userRepo = new UserRepository();
    }

    public function showForm(): void
    {
        $errors = [];
        $success = '';

        $email = strtolower(trim((string)($_GET['email'] ?? '')));
        $token = (string)($_GET['token'] ?? '');

        if (!$this->resetRepo->isValidToken($email, $token)) {
            $errors[] = 'Lien de réinitialisation invalide ou expiré.';
        }

        View::render('auth/reset-password', compact('errors', 'success', 'email', 'token'));
    }

    public function submit(): void
    {
        $errors = [];
        $success = '';

        $csrf     = $_POST['csrf_token'] ?? '';
        $token    = $_POST['token'] ?? '';
        $emailPosted = strtolower(trim($_POST['email'] ?? '')); // Pour réaffichage
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['password_confirm'] ?? '';

        // --- VALIDATIONS ---
        if (!Csrf::validate($csrf)) $errors[] = 'Session expirée.';
        if ($token === '') $errors[] = 'Lien invalide.';
        if ($password !== $confirm) $errors[] = 'Mots de passe différents.';

        // Complexité mot de passe
        if (strlen($password) < 12
            || !preg_match('/[A-Z]/', $password)
            || !preg_match('/[a-z]/', $password)
            || !preg_match('/\d/', $password)
            || !preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = 'Mot de passe trop faible (12+ car., maj/min/chiffre/spécial).';
        }

        if (!$errors) {
            $pdo = Database::getConnection(); // On a besoin de PDO pour la Transaction

            try {
                $pdo->beginTransaction();

                // 1. Récupérer l'email depuis le token (via Repo)
                $emailFromToken = $this->resetRepo->getEmailFromToken($token);

                if (!$emailFromToken) {
                    $pdo->rollBack();
                    $errors[] = 'Lien invalide ou expiré.';
                } else {
                    // 2. Mettre à jour le mot de passe (via UserRepo)
                    // Il faut d'abord récupérer l'ID de l'user par son email
                    $user = $this->userRepo->findByEmail($emailFromToken);

                    if ($user) {
                        $hash = password_hash($password, PASSWORD_DEFAULT);
                        $this->userRepo->updatePassword($user->getId(), $hash);

                        // 3. Invalider le token (via ResetRepo)
                        $this->resetRepo->markAsUsed($token);

                        $pdo->commit();
                        header('Location: /login?reset=1');
                        exit;
                    } else {
                        // Email trouvé dans le token mais pas dans la table User
                        $pdo->rollBack();
                        $errors[] = 'Utilisateur introuvable.';
                    }
                }
            } catch (\Throwable $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                error_log('[RESET] Erreur : ' . $e->getMessage());
                $errors[] = 'Erreur technique. Réessayez.';
            }
        }

        View::render('auth/reset-password', [
            'errors'  => $errors,
            'success' => $success,
            'email'   => $emailPosted,
            'token'   => $token,
        ]);
    }
}