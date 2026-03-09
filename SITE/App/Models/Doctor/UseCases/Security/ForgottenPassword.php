<?php

declare(strict_types=1);

namespace App\Models\Doctor\UseCases\Security;

use App\Models\Doctor\Interfaces\IDoctorRepository;
use App\Models\Doctor\Interfaces\ISecurityWriteRepository;
use App\Models\Doctor\Validators\DoctorValidator;
use Core\Services\MailerService;

class ForgottenPassword
{
    private IDoctorRepository $repo;
    private ISecurityWriteRepository $writeRepo;
    private DoctorValidator $validator;
    private MailerService $mailer;

    public function __construct(
        IDoctorRepository $repo,
        ISecurityWriteRepository $writeRepo,
        DoctorValidator $validator,
        MailerService $mailer
    ) {
        $this->repo = $repo;
        $this->writeRepo = $writeRepo;
        $this->validator = $validator;
        $this->mailer = $mailer;
    }

    public function execute(string $email): void
    {
        $emailError = $this->validator->validateEmail($email);
        if ($emailError) {
            return;
        }

        $doctor = $this->repo->findByEmail($email);
        if (!$doctor) return;

        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $expiresAt = date('Y-m-d H:i:s', time() + 3600);

        $this->writeRepo->storeResetToken($email, $tokenHash, $expiresAt);

        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $domain = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $resetUrl = "{$protocol}://{$domain}/reset-password?token={$token}&email={$email}";

        $this->mailer->send(
            $email,
            'Réinitialisation de votre mot de passe - DashMed',
            'reset-password',
            ['name' => $doctor->getPrenom() . ' ' . $doctor->getNom(), 'url' => $resetUrl]
        );
    }
}