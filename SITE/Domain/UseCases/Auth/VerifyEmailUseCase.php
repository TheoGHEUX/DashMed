<?php

namespace Domain\UseCases\Auth;

use Domain\Interfaces\VerifyEmailUseCaseInterface;
use Core\Interfaces\UserRepositoryInterface;

class VerifyEmailUseCase implements VerifyEmailUseCaseInterface
{
    private UserRepositoryInterface $userRepo;

    public function __construct(UserRepositoryInterface $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    public function execute(string $token): array
    {
        if (empty($token)) {
            return ['success' => false, 'errors' => ['Token manquant.'], 'message' => ''];
        }

        // 1. Trouver l'utilisateur via le token
        $user = $this->userRepo->findByVerificationToken($token);

        if (!$user) {
            return ['success' => false, 'errors' => ['Lien invalide ou expiré.'], 'message' => ''];
        }

        // 2. Déjà vérifié ?
        if ($user->isEmailVerified()) {
            return ['success' => true, 'errors' => [], 'message' => 'Votre email est déjà vérifié. Connectez-vous.'];
        }

        // 3. Vérifier l'expiration (24h)
        $now = new \DateTime();
        $expires = new \DateTime($user->getVerificationExpires());

        if ($now > $expires) {
            return ['success' => false, 'errors' => ['Ce lien a expiré.'], 'message' => ''];
        }

        // 4. Valider
        if ($this->userRepo->verifyEmailToken($token)) {
            return ['success' => true, 'errors' => [], 'message' => 'Email vérifié avec succès !'];
        }

        return ['success' => false, 'errors' => ['Erreur technique lors de la validation.'], 'message' => ''];
    }
}
