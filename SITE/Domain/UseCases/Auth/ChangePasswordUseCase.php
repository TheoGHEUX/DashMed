<?php

namespace Domain\UseCases\Auth;

use Core\Interfaces\ChangePasswordUseCaseInterface;
use Core\Interfaces\UserRepositoryInterface;

class ChangePasswordUseCase implements ChangePasswordUseCaseInterface
{
    private UserRepositoryInterface $userRepo;

    public function __construct(UserRepositoryInterface $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    public function execute(int $userId, string $oldPassword, string $newPassword, string $confirmPassword): array
    {
        $errors = [];

        // 1. Validation de base
        if ($newPassword !== $confirmPassword) {
            return ['success' => false, 'errors' => ['Les mots de passe ne correspondent pas.'], 'message' => ''];
        }

        // 2. Complexité
        if (strlen($newPassword) < 12 ||
            !preg_match('/[A-Z]/', $newPassword) ||
            !preg_match('/[a-z]/', $newPassword) ||
            !preg_match('/\d/', $newPassword) ||
            !preg_match('/[^A-Za-z0-9]/', $newPassword)) {
            return ['success' => false, 'errors' => ['Le mot de passe doit faire 12 caractères min. avec Maj, Min, Chiffre et Caractère spécial.'], 'message' => ''];
        }

        // 3. Récupération utilisateur
        $user = $this->userRepo->findById($userId);
        if (!$user) {
            return ['success' => false, 'errors' => ['Utilisateur introuvable.'], 'message' => ''];
        }

        // 4. Vérification Ancien Mot de passe
        if (!password_verify($oldPassword, $user->getPasswordHash())) {
            return ['success' => false, 'errors' => ['Ancien mot de passe incorrect.'], 'message' => ''];
        }

        // 5. Mise à jour
        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $updated = $this->userRepo->updatePassword($userId, $newHash);

        if ($updated) {
            return ['success' => true, 'errors' => [], 'message' => 'Mot de passe mis à jour avec succès.'];
        }

        return ['success' => false, 'errors' => ['Erreur technique lors de la mise à jour.'], 'message' => ''];
    }
}
