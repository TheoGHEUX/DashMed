<?php

declare(strict_types=1);

namespace App\Models\Doctor\UseCases\Authentication;

use App\Models\Doctor\Interfaces\IDoctorVerificationRepository;

class VerifyEmail
{
    private IDoctorVerificationRepository $repository;

    public function __construct(IDoctorVerificationRepository $repository)
    {
        $this->repository = $repository;
    }

    public function execute(string $token): array
    {
        $user = $this->repository->findByVerificationToken($token);

        if (!$user) {
            return ['success' => false, 'error' => 'Ce lien de validation est invalide.'];
        }

        if ($user->isEmailVerified()) {
            return ['success' => true, 'message' => 'Votre email est déjà vérifié. Vous pouvez vous connecter.'];
        }

        $expiresStr = $user->getVerificationExpires();
        if (!$expiresStr || new \DateTime() > new \DateTime($expiresStr)) {
            return ['success' => false, 'error' => 'Ce lien a expiré. Veuillez demander un nouvel email.'];
        }

        if ($this->repository->verifyEmailToken($token)) {
            return ['success' => true, 'message' => 'Email vérifié avec succès ! Bienvenue.'];
        }

        return ['success' => false, 'error' => 'Une erreur technique est survenue.'];
    }
}