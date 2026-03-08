<?php

declare(strict_types=1);

namespace App\Models\Doctor\UseCases\Authentication;

use App\Models\Doctor\Interfaces\IDoctorReadRepository;
use App\Models\Doctor\Interfaces\IDoctorVerificationRepository;
use App\Services\AuthMailer; // Service Métier

class ResendVerificationEmail
{
    private IDoctorReadRepository $readRepo;
    private IDoctorVerificationRepository $verifyRepo;
    private AuthMailer $mailer;

    public function __construct(
        IDoctorReadRepository $readRepo,
        IDoctorVerificationRepository $verifyRepo,
        AuthMailer $mailer
    ) {
        $this->readRepo = $readRepo;
        $this->verifyRepo = $verifyRepo;
        $this->mailer = $mailer;
    }

    public function execute(string $email): array
    {
        $user = $this->readRepo->findByEmail($email);

        if (!$user) {
            return ['success' => true, 'message' => 'Si ce compte existe, un email a été envoyé.'];
        }

        if ($user->isEmailVerified()) {
            return ['success' => false, 'error' => 'Ce compte est déjà vérifié. Connectez-vous.'];
        }

        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));
        $this->verifyRepo->setVerificationToken($email, $token, $expires);

        // Appel simplifié via le Service
        $sent = $this->mailer->sendVerification($email, $user->getPrenom(), $token);

        if (!$sent) {
            return ['success' => false, 'error' => 'Erreur technique lors de l\'envoi de l\'email.'];
        }

        return ['success' => true, 'message' => 'Un nouveau lien de vérification a été envoyé à votre adresse.'];
    }
}