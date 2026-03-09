<?php

declare(strict_types=1);

namespace App\Models\Doctor\UseCases\Authentication;

use App\Models\Doctor\Interfaces\IDoctorRepository;
use App\Models\Doctor\Interfaces\IDoctorVerificationRepository;
use App\Models\Doctor\Validators\DoctorValidator;
use Core\Services\MailerService;

class RegisterDoctor
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

    public function execute(array $data): array
    {
        $validationErrors = $this->validator->validateRegistration($data);
        if (!empty($validationErrors)) {
            return ['success' => false, 'errors' => $validationErrors];
        }

        if ($this->repo->findByEmail($data['email'])) {
            return ['success' => false, 'errors' => ['Cet email est déjà utilisé.']];
        }

        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

        $success = $this->repo->create([
            'prenom' => $data['prenom'],
            'nom' => $data['nom'],
            'email' => $data['email'],
            'password_hash' => $hashedPassword,
            'specialite' => $data['specialite'],
            'sexe' => $data['sexe'] ?? null
        ]);

        if (!$success) {
            return ['success' => false, 'errors' => ['Erreur technique lors de l\'enregistrement.']];
        }

        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));
        $this->verifyRepo->setVerificationToken($data['email'], $token, $expires);

        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $domain = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $url = "{$protocol}://{$domain}/verify-email?token=$token";
        $this->mailer->send(
            $data['email'],
            'Bienvenue sur DashMed - Confirmez votre compte',
            'verify-email',
            ['name' => $data['prenom'] ?? '', 'url' => $url]
        );

        return ['success' => true];
    }
}