<?php

namespace Domain\UseCases\Auth;

use Domain\Interfaces\ResendVerificationEmailUseCaseInterface;
use Core\Interfaces\UserRepositoryInterface;
use Core\Mailer;

class ResendVerificationEmailUseCase implements ResendVerificationEmailUseCaseInterface
{
    private UserRepositoryInterface $userRepo;

    public function __construct(UserRepositoryInterface $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    public function execute(string $email): array
    {
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'errors' => ['Email invalide.'], 'message' => ''];
        }

        $user = $this->userRepo->findByEmail($email);

        // Sécurité : On ne dit pas si l'email n'existe pas
        if (!$user) {
            return ['success' => true, 'errors' => [], 'message' => 'Si ce compte existe, un email a été envoyé.'];
        }

        if ($user->isEmailVerified()) {
            return ['success' => false, 'errors' => ['Ce compte est déjà vérifié.'], 'message' => ''];
        }

        // Génération nouveau token
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));

        $this->userRepo->setVerificationToken($email, $token, $expires);

        // Envoi
        Mailer::sendEmailVerification($email, $user->getPrenom(), $token);

        return ['success' => true, 'errors' => [], 'message' => 'Un nouveau lien a été envoyé.'];
    }
}
