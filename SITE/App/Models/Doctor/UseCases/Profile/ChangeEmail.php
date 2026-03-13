<?php

declare(strict_types=1);

namespace App\Models\Doctor\UseCases\Profile;

use App\Exceptions\ValidationException;
use App\Interfaces\IMailer;
use App\Models\Doctor\Interfaces\IDoctorRepository;
use App\Models\Doctor\Interfaces\IDoctorVerificationRepository;
use App\Models\Doctor\Validators\DoctorValidator;
use App\ValueObjects\Email;
use Core\Services\TokenGenerator;
use Core\Services\UrlBuilder;

/**
 * Use Case: traite le changement d’email d’un médecin.
 *
 * Gère la vérification du mot de passe, la validation et l’envoi mail de confirmation.
 */
final class ChangeEmail
{
    private IDoctorRepository $repo;
    private IDoctorVerificationRepository $verifyRepo;
    private DoctorValidator $validator;
    private IMailer $mailer;

    public function __construct(
        IDoctorRepository $repo,
        IDoctorVerificationRepository $verifyRepo,
        DoctorValidator $validator,
        IMailer $mailer
    ) {
        $this->repo = $repo;
        $this->verifyRepo = $verifyRepo;
        $this->validator = $validator;
        $this->mailer = $mailer;
    }

    /**
     * Exécute le changement d’email: validation, update DB, envoi mail avec nouveau lien.
     */
    public function execute(int $userId, string $currentPassword, string $newEmail): array
    {
        try {
            $emailVO = new Email($newEmail);
        } catch (ValidationException $e) {
            return ['success' => false, 'error' => implode("\n", array_values($e->getErrors()))];
        }

        $emailError = $this->validator->validateEmail($newEmail);
        if ($emailError) {
            return ['success' => false, 'error' => $emailError];
        }

        $user = $this->repo->findById($userId);
        if (!$user || !password_verify($currentPassword, $user->getPasswordHash())) {
            return ['success' => false, 'error' => 'Mot de passe incorrect ou utilisateur introuvable.'];
        }

        if ($this->repo->emailExists($emailVO->getValue())) {
            return ['success' => false, 'error' => 'Cet email est déjà utilisé.'];
        }

        if (!$this->repo->updateEmail($userId, $emailVO->getValue())) {
            return ['success' => false, 'error' => 'Erreur technique.'];
        }

        $tokenData = TokenGenerator::generateWithExpiry(32, '+24 hours');
        $this->verifyRepo->setVerificationToken($emailVO->getValue(), $tokenData['token'], $tokenData['expires']);

        $url = UrlBuilder::build('/verify-email', ['token' => $tokenData['token']]);

        $this->mailer->send(
            $emailVO->getValue(),
            'Confirmez votre nouvelle adresse email - DashMed',
            'verify-email',
            ['name' => $user->getPrenom(), 'url' => $url]
        );

        return ['success' => true, 'message' => 'Adresse mise à jour. Vérifiez votre nouvelle boîte mail.'];
    }
}