<?php

declare(strict_types=1);

namespace App\Models\Doctor\UseCases\Authentication;

use App\Interfaces\IMailer;
use App\Models\Doctor\Interfaces\IDoctorRepository;
use App\Models\Doctor\Interfaces\IDoctorVerificationRepository;
use App\Models\Doctor\Validators\DoctorValidator;
use App\ValueObjects\Email;
use App\ValueObjects\Password;
use App\Exceptions\ValidationException;
use Core\Services\TokenGenerator;
use Core\Services\UrlBuilder;

/**
 * Use Case pour l’inscription d’un nouveau médecin.
 * Gère toutes les validations métier, l'envoi du mail de validation et la création.
 */
final class RegisterDoctor
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
     * Exécute l’enregistrement, la vérification, et le mail d’accueil.
     * Retourne ['success'=>bool, 'errors'=>array] en cas d’échec.
     */
    public function execute(array $data): array
    {
        // Validation via le validator
        $validationErrors = $this->validator->validateRegistration($data);
        if (!empty($validationErrors)) {
            return ['success' => false, 'errors' => $validationErrors];
        }

        try {
            $email = new Email($data['email']);
            $password = new Password($data['password']);
        } catch (ValidationException $e) {
            return ['success' => false, 'errors' => $e->getErrors()];
        }

        // Vérifier si l'email existe déjà
        if ($this->repo->findByEmail($email->getValue())) {
            return ['success' => false, 'errors' => ['Cet email est déjà utilisé.']];
        }

        // Créer le médecin avec le mot de passe hashé
        $success = $this->repo->create([
            'prenom' => $data['prenom'],
            'nom' => $data['nom'],
            'email' => $email->getValue(),
            'password_hash' => $password->hash(),
            'specialite' => $data['specialite'],
            'sexe' => $data['sexe'] ?? null
        ]);

        if (!$success) {
            return ['success' => false, 'errors' => ['Erreur technique lors de l\'enregistrement.']];
        }

        // Générer le token de vérification
        $tokenData = TokenGenerator::generateWithExpiry(32, '+24 hours');
        $this->verifyRepo->setVerificationToken(
            $email->getValue(),
            $tokenData['token'],
            $tokenData['expires']
        );

        // Construire l'URL de vérification
        $url = UrlBuilder::build('/verify-email', ['token' => $tokenData['token']]);

        // Envoyer l'email de vérification
        $this->mailer->send(
            $email->getValue(),
            'Bienvenue sur DashMed - Confirmez votre compte',
            'verify-email',
            ['name' => $data['prenom'] ?? '', 'url' => $url]
        );

        return ['success' => true];
    }
}