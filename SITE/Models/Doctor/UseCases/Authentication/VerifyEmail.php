<?php

declare(strict_types=1);

namespace Models\Doctor\UseCases\Authentication;

use Models\Doctor\Interfaces\IDoctorVerificationRepository;

class VerifyEmail
{
    private IDoctorVerificationRepository $repository;

    public function __construct(IDoctorVerificationRepository $repository)
    {
        $this->repository = $repository;
    }

    public function execute(string $token): array
    {
        // 1. Chercher le médecin associé à ce token
        $user = $this->repository->findByVerificationToken($token);

        if (!$user) {
            return ['success' => false, 'error' => 'Ce lien de validation est invalide.'];
        }

        // 2. Vérifier si déjà validé
        if ($user->isEmailVerified()) {
            return ['success' => true, 'message' => 'Votre email est déjà vérifié. Vous pouvez vous connecter.'];
        }

        // 3. Vérifier l'expiration
        $now = new \DateTime();
        $expiresStr = $user->getVerificationExpires();

        if (!$expiresStr) {
            return ['success' => false, 'error' => 'Token invalide.'];
        }

        $expires = new \DateTime($expiresStr);

        if ($now > $expires) {
            return ['success' => false, 'error' => 'Ce lien a expiré. Veuillez demander un nouvel email de validation.'];
        }

        // 4. Valider le compte
        if ($this->repository->verifyEmailToken($token)) {
            return ['success' => true, 'message' => 'Email vérifié avec succès ! Bienvenue.'];
        }

        return ['success' => false, 'error' => 'Une erreur technique est survenue lors de la validation.'];
    }
}