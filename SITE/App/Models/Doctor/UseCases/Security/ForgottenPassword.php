<?php

declare(strict_types=1);

namespace App\Models\Doctor\UseCases\Security;

use App\Models\Doctor\Interfaces\IDoctorRepository;
use App\Models\Doctor\Interfaces\ISecurityRepository;
use App\Models\Doctor\Validators\DoctorValidator;
use Core\Services\MailerService;

class ForgottenPassword
{
    private IDoctorRepository $repo;
    private ISecurityRepository $securityRepo;
    private DoctorValidator $validator;
    private MailerService $mailer;

    public function __construct(
        IDoctorRepository $repo,
        ISecurityRepository $securityRepo,
        DoctorValidator $validator,
        MailerService $mailer
    ) {
        $this->repo = $repo;
        $this->securityRepo = $securityRepo;
        $this->validator = $validator;
        $this->mailer = $mailer;
    }

    public function execute(string $email): void
    {
        $doctor = $this->repo->findByEmail($email);
        if (!$doctor) return;

        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $expiresAt = date('Y-m-d H:i:s', time() + 3600);

        $this->securityRepo->storeResetToken($email, $tokenHash, $expiresAt);

        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $domain = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $resetUrl = "{$protocol}://{$domain}/reset-password?token={$token}&email=" . urlencode($email);

        $this->mailer->send(
            $email,
            'Réinitialisation de votre mot de passe - DashMed',
            'reset-password',
            [
                'name' => $doctor->getPrenom() . ' ' . $doctor->getNom(),
                'url' => $resetUrl
            ]
        );
    }
}