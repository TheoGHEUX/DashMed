<?php

declare(strict_types=1);

namespace App\Models\Doctor\UseCases\Profile;

use App\Models\Doctor\Interfaces\IDoctorRepository;
use App\Models\Doctor\Interfaces\IDoctorVerificationRepository;
use App\Models\Doctor\Validators\DoctorValidator;
use Core\Services\MailerService;

class ChangeEmail
{
    private IDoctorRepository $repo;
    private IDoctorVerificationRepository $verifyRepo;
    private DoctorValidator $validator;
    private MailerService $mailer;

    public function __construct(
        IDoctorRepository $repo,
        IDoctorVerificationRepository $verifyRepo,
        DoctorValidator $validator,
        MailerService $mailer
    ) {
        $this->repo = $repo;
        $this->verifyRepo = $verifyRepo;
        $this->validator = $validator;
        $this->mailer = $mailer;
    }

    public function execute(int $userId, string $currentPassword, string $newEmail): array
    {
        $emailError = $this->validator->validateEmail($newEmail);
        if ($emailError) {
            return ['success' => false, 'error' => $emailError];
        }

        $user = $this->repo->findById($userId);
        if (!$user || !password_verify($currentPassword, $user->getPasswordHash())) {
            return ['success' => false, 'error' => 'Mot de passe incorrect ou utilisateur introuvable.'];
        }

        if ($this->repo->emailExists($newEmail)) {
            return ['success' => false, 'error' => 'Cet email est déjà utilisé.'];
        }

        if (!$this->repo->updateEmail($userId, $newEmail)) {
            return ['success' => false, 'error' => 'Erreur technique.'];
        }

        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));
        $this->verifyRepo->setVerificationToken($newEmail, $token, $expires);

        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $domain = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $url = "{$protocol}://{$domain}/verify-email?token=$token";

        $this->mailer->send(
            $newEmail,
            'Confirmez votre nouvelle adresse email - DashMed',
            'verify-email',
            ['name' => $user->getPrenom(), 'url' => $url]
        );

        return ['success' => true, 'message' => 'Adresse mise à jour. Vérifiez votre nouvelle boîte mail.'];
    }
}