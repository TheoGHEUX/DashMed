<?php

declare(strict_types=1);

namespace App\Models\Doctor\UseCases\Profile;

use App\Models\Auth\Repositories\PasswordResetRepository;
use App\Models\Doctor\Repositories\DoctorReadRepository;
use App\Models\Doctor\Repositories\DoctorWriteRepository;

class ResetPassword
{
    private PasswordResetRepository $resetRepo;
    private DoctorReadRepository $doctorRead;
    private DoctorWriteRepository $doctorWrite;

    public function __construct(
        PasswordResetRepository $resetRepo,
        DoctorReadRepository $doctorRead,
        DoctorWriteRepository $doctorWrite
    ) {
        $this->resetRepo = $resetRepo;
        $this->doctorRead = $doctorRead;
        $this->doctorWrite = $doctorWrite;
    }

    public function execute(string $email, string $token, string $newPassword): array
    {
        // 1. Récupérer le token en base
        $resetEntry = $this->resetRepo->findByEmail($email);

        if (!$resetEntry) {
            return ['success' => false, 'error' => 'Lien invalide ou expiré.'];
        }

        // 2. Vérifier que le hash correspond
        // On re-hash le token reçu pour comparer avec celui stocké
        $tokenHash = hash('sha256', $token);

        if (!hash_equals($resetEntry['token_hash'], $tokenHash)) {
            return ['success' => false, 'error' => 'Token invalide.'];
        }

        // 3. Trouver le médecin
        $doctor = $this->doctorRead->findByEmail($email);
        if (!$doctor) {
            return ['success' => false, 'error' => 'Utilisateur introuvable.'];
        }

        // 4. Mettre à jour le mot de passe
        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $this->doctorWrite->updatePassword($doctor->getId(), $newHash);

        // 5. Supprimer le token utilisé
        $this->resetRepo->deleteByEmail($email);

        return ['success' => true];
    }
}