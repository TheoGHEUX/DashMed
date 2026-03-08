<?php

declare(strict_types=1);

namespace Models\Doctor\UseCases\Profile;

// Vos namespaces actuels
use Models\Doctor\Interfaces\IDoctorReadRepository;
use Models\Doctor\Interfaces\IDoctorWriteRepository;

class ChangePassword
{
    private IDoctorReadRepository $readRepo;
    private IDoctorWriteRepository $writeRepo;

    public function __construct(IDoctorReadRepository $read, IDoctorWriteRepository $write)
    {
        $this->readRepo = $read;
        $this->writeRepo = $write;
    }

    public function execute(int $userId, string $oldPassword, string $newPassword, string $confirmPassword): array
    {
        // 1. Validation confirmation
        if ($newPassword !== $confirmPassword) {
            return ['success' => false, 'error' => 'Les mots de passe ne correspondent pas.'];
        }

        // 2. Validation complexité (Règle métier)
        if (strlen($newPassword) < 12
            || !preg_match('/[A-Z]/', $newPassword)
            || !preg_match('/[a-z]/', $newPassword)
            || !preg_match('/\d/', $newPassword)
            || !preg_match('/[^A-Za-z0-9]/', $newPassword)) {
            return [
                'success' => false,
                'error' => 'Le mot de passe doit faire 12 caractères min. avec Maj, Min, Chiffre et Caractère spécial.'
            ];
        }

        // 3. Récupération user
        $user = $this->readRepo->findById($userId);
        if (!$user) {
            return ['success' => false, 'error' => 'Utilisateur introuvable.'];
        }

        // 4. Vérification ancien MDP
        if (!password_verify($oldPassword, $user->getPasswordHash())) {
            return ['success' => false, 'error' => "L'ancien mot de passe est incorrect."];
        }

        // 5. Hash & Update
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);

        if ($this->writeRepo->updatePassword($userId, $hash)) {
            return ['success' => true, 'message' => 'Mot de passe modifié avec succès.'];
        }

        return ['success' => false, 'error' => 'Erreur technique.'];
    }
}