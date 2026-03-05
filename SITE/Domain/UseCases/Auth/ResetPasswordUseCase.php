<?php

namespace Domain\UseCases\Auth;

use Core\Database;
use Core\Interfaces\UserRepositoryInterface;
use Models\Repositories\PasswordResetRepository;
use Exception;

class ResetPasswordUseCase
{
    private UserRepositoryInterface $userRepo;
    private PasswordResetRepository $pwdRepo;

    public function __construct(UserRepositoryInterface $userRepo, PasswordResetRepository $pwdRepo)
    {
        $this->userRepo = $userRepo;
        $this->pwdRepo = $pwdRepo;
    }

    /**
     * Exécute la réinitialisation
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function execute(string $token, string $password, string $confirm): array
    {
        // 1. Validation Mot de passe
        if ($password !== $confirm) {
            return ['success' => false, 'error' => 'Les mots de passe ne correspondent pas.'];
        }

        // Regex : 12 car, 1 Maj, 1 min, 1 chiffre, 1 spécial
        if (strlen($password) < 12
            || !preg_match('/[A-Z]/', $password)
            || !preg_match('/[a-z]/', $password)
            || !preg_match('/\d/', $password)
            || !preg_match('/[^A-Za-z0-9]/', $password)) {
            return ['success' => false, 'error' => 'Mot de passe trop faible (12 car, Maj, min, chiffre, spécial).'];
        }

        // 2. Vérification du Token
        // Le token dans l'URL est brut, on le hashe pour comparer avec la BDD
        $tokenHash = hash('sha256', $token);
        $email = $this->pwdRepo->findEmailByToken($tokenHash);

        if (!$email) {
            return ['success' => false, 'error' => 'Ce lien est invalide ou a expiré.'];
        }

        // 3. Récupération User
        $user = $this->userRepo->findByEmail($email);
        if (!$user) {
            return ['success' => false, 'error' => 'Utilisateur introuvable.'];
        }

        // 4. Transaction SQL (Update User + Delete Token)
        $pdo = Database::getConnection();

        try {
            if (!$pdo->inTransaction()) {
                $pdo->beginTransaction();
            }

            // Hashage et Mise à jour
            $newPasswordHash = password_hash($password, PASSWORD_DEFAULT);
            $this->userRepo->updatePassword($user->getId(), $newPasswordHash);

            // Invalidation du token (on supprime tout pour cet email)
            $this->pwdRepo->deleteByEmail($email);

            $pdo->commit();
            return ['success' => true, 'error' => null];

        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            // Log réel pour le développeur
            error_log("Erreur ResetPassword : " . $e->getMessage());
            return ['success' => false, 'error' => 'Une erreur technique est survenue.'];
        }
    }
}
