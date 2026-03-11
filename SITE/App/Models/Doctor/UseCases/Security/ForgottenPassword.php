<?php

declare(strict_types=1);

namespace App\Models\Doctor\UseCases\Security;

use App\Exceptions\ValidationException;
use App\Models\Doctor\Interfaces\IDoctorRepository;
use App\Models\Doctor\Interfaces\ISecurityRepository;
use App\Models\Doctor\Validators\DoctorValidator;
use App\ValueObjects\Email;
use Core\Services\MailerService;
use Core\Services\TokenGenerator;
use Core\Services\UrlBuilder;

final class ForgottenPassword
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
        try {
            $emailVO = new Email($email);
        } catch (ValidationException $e) {
            return;
        }

        $doctor = $this->repo->findByEmail($emailVO->getValue());
        if (!$doctor) return;

        $token = TokenGenerator::generate();
        $tokenHash = TokenGenerator::hash($token);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $this->securityRepo->storeResetToken($emailVO->getValue(), $tokenHash, $expiresAt);

        $resetUrl = UrlBuilder::build('/reset-password', [
            'token' => $token,
            'email' => $emailVO->getValue(),
        ]);

        $this->mailer->send(
            $emailVO->getValue(),
            'Réinitialisation de votre mot de passe - DashMed',
            'reset-password',
            [
                'name' => $doctor->getPrenom() . ' ' . $doctor->getNom(),
                'url' => $resetUrl
            ]
        );
    }
}