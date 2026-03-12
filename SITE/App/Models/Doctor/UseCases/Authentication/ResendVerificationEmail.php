<?php

declare(strict_types=1);

namespace App\Models\Doctor\UseCases\Authentication;

use App\Exceptions\ValidationException;
use App\Interfaces\IMailer;
use App\Models\Doctor\Interfaces\IDoctorRepository;
use App\Models\Doctor\Interfaces\IResendVerificationEmail;
use App\Models\Doctor\Interfaces\IDoctorVerificationRepository;
use App\ValueObjects\Email;
use Core\Services\TokenGenerator;
use Core\Services\UrlBuilder;

/**
 * Use case pour le renvoi d'email de vérification médecin.
 *
 * Un use case (cas d'usage) regroupe la logique métier pour une action précise du domaine.
 * Il orchestre les appels aux repositories, validators, etc., pour réaliser une tâche métier complète.
 */
final class ResendVerificationEmail implements IResendVerificationEmail
{
    private IDoctorRepository $repo;
    private IDoctorVerificationRepository $verifyRepo;
    private IMailer $mailer;

    public function __construct(
        IDoctorRepository $repo,
        IDoctorVerificationRepository $verifyRepo,
        IMailer $mailer
    ) {
        $this->repo = $repo;
        $this->verifyRepo = $verifyRepo;
        $this->mailer = $mailer;
    }

    public function execute(string $email): array
    {
        try {
            $emailVO = new Email($email);
        } catch (ValidationException $e) {
            return ['success' => true, 'message' => 'Si ce compte existe, un email a ete envoye.'];
        }

        $user = $this->repo->findByEmail($emailVO->getValue());

        if (!$user) {
            return ['success' => true, 'message' => 'Si ce compte existe, un email a ete envoye.'];
        }

        if ($user->getVerificationToken() === null) {
            return ['success' => false, 'error' => 'Ce compte est déjà vérifié. Connectez-vous.'];
        }

        $tokenData = TokenGenerator::generateWithExpiry(32, '+24 hours');
        $this->verifyRepo->setVerificationToken($emailVO->getValue(), $tokenData['token'], $tokenData['expires']);
        $url = UrlBuilder::build('/verify-email', ['token' => $tokenData['token']]);

        $sent = $this->mailer->send(
            $emailVO->getValue(),
            'Confirmez votre adresse email - DashMed',
            'verify_email',
            [
                'name' => $user->getPrenom(),
                'url' => $url,
            ]
        );

        if (!$sent) {
            return ['success' => false, 'error' => 'Erreur technique lors de l\'envoi de l\'email.'];
        }

        return ['success' => true, 'message' => 'Un nouveau lien de vérification a été envoyé à votre adresse.'];
    }
}
