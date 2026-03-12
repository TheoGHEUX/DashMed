<?php

declare(strict_types=1);

namespace App\Models\Doctor\UseCases\Security;

use App\Exceptions\ValidationException;
use App\Interfaces\IMailer;
use App\Models\Doctor\Interfaces\IDoctorRepository;
use App\Models\Doctor\Interfaces\ISecurityRepository;
use App\Models\Doctor\Validators\DoctorValidator;
use App\ValueObjects\Email;
use Core\Services\TokenGenerator;
use Core\Services\UrlBuilder;

/**
 * Use case pour la demande de réinitialisation de mot de passe médecin.
 *
 * Un use case (cas d'usage) regroupe la logique métier pour une action précise du domaine.
 * Il orchestre les appels aux repositories, validators, etc., pour réaliser une tâche métier complète.
 */
final class ForgottenPassword
{
    private IDoctorRepository $repo;
    private ISecurityRepository $securityRepo;
    private IMailer $mailer;

    public function __construct(
        IDoctorRepository $repo,
        ISecurityRepository $securityRepo,
        IMailer $mailer
    ) {
        $this->repo = $repo;
        $this->securityRepo = $securityRepo;
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
        if (!$doctor) {
            return;
        }

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
