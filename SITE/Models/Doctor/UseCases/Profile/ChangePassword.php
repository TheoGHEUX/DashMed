<?php

declare(strict_types=1);

namespace Models\Doctor\UseCases\Profile;

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

    public function execute(int $userId, string $oldPassword, string $newPassword): array
    {
        // 1. Récupérer l'utilisateur actuel
        $user = $this->readRepo->findById($userId);

        if (!$user) {
            return ['success' => false, 'error' => 'Utilisateur introuvable.'];
        }

        // 2. Vérifier l'ancien mot de passe
        if (!password_verify($oldPassword, $user->getPasswordHash())) {
            return ['success' => false, 'error' => "L'ancien mot de passe est incorrect."];
        }

        // 3. Hasher le nouveau mot de passe
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);

        // 4. Mettre à jour
        if ($this->writeRepo->updatePassword($userId, $hash)) {
            return ['success' => true, 'message' => 'Mot de passe modifié avec succès.'];
        }

        return ['success' => false, 'error' => 'Erreur technique lors de la mise à jour.'];
    }
}