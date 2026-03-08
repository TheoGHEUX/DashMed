<?php

declare(strict_types=1);

namespace App\Models\Doctor\UseCases\Profile;

use App\Models\Doctor\Interfaces\IDoctorReadRepository;
use App\Models\Doctor\Interfaces\IDoctorWriteRepository;
use App\Models\Doctor\Interfaces\IDoctorVerificationRepository;
use App\Services\AuthMailer;

class ChangeEmail
{
    private IDoctorReadRepository $readRepo;
    private IDoctorWriteRepository $writeRepo;
    private IDoctorVerificationRepository $verifyRepo;
    private AuthMailer $mailer;

    public function __construct(
        IDoctorReadRepository $read,
        IDoctorWriteRepository $write,
        IDoctorVerificationRepository $verify,
        AuthMailer $mailer
    ) {
        $this->readRepo = $read;
        $this->writeRepo = $write;
        $this->verifyRepo = $verify;
        $this->mailer = $mailer;
    }

    public function execute(int $userId, string $currentPassword, string $newEmail): array
    {
        $user = $this->readRepo->findById($userId);
        if (!$user || !password_verify($currentPassword, $user->getPasswordHash())) {
            return ['success' => false, 'error' => 'Mot de passe incorrect ou utilisateur introuvable.'];
        }

        if ($this->readRepo->emailExists($newEmail)) {
            return ['success' => false, 'error' => 'Cet email est déjà utilisé.'];
        }

        if (!$this->writeRepo->updateEmail($userId, $newEmail)) {
            return ['success' => false, 'error' => 'Erreur technique.'];
        }

        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));
        $this->verifyRepo->setVerificationToken($newEmail, $token, $expires);

        // Appel simplifié via le Service
        $this->mailer->sendVerification($newEmail, $user->getPrenom(), $token);

        return ['success' => true, 'message' => 'Adresse mise à jour. Vérifiez votre nouvelle boîte mail.'];
    }
}