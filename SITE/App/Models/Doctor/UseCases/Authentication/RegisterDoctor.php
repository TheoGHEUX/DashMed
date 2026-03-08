<?php

declare(strict_types=1);

namespace App\Models\Doctor\UseCases\Authentication;

use App\Interfaces\IMailer;
use App\Interfaces\IDoctorReadRepository;
use App\Interfaces\IDoctorWriteRepository;
use App\Interfaces\IDoctorVerificationRepository;

class RegisterDoctor
{
    private IDoctorReadRepository $readRepo;
    private IDoctorWriteRepository $writeRepo;
    private IDoctorVerificationRepository $verifyRepo;
    private IMailer $mailer;

    public function __construct(
        IDoctorReadRepository $readRepo,
        IDoctorWriteRepository $writeRepo,
        IDoctorVerificationRepository $verifyRepo,
        IMailer $mailer
    ) {
        $this->readRepo = $readRepo;
        $this->writeRepo = $writeRepo;
        $this->verifyRepo = $verifyRepo;
        $this->mailer = $mailer;
    }

    public function execute(array $data): array
    {
        // 1. REGEX : Validation de la complexité du mot de passe (Règle Métier)
        if (strlen($data['password']) < 12
            || !preg_match('/[A-Z]/', $data['password'])
            || !preg_match('/[a-z]/', $data['password'])
            || !preg_match('/\d/', $data['password'])
            || !preg_match('/[^A-Za-z0-9]/', $data['password'])) {
            return [
                'success' => false,
                'error' => 'Le mot de passe doit faire 12 caractères min. avec Maj, Min, Chiffre et Caractère spécial.'
            ];
        }

        // 2. Validation de l'unicité de l'email
        if ($this->readRepo->emailExists($data['email'])) {
            return ['success' => false, 'error' => 'Cet email est déjà utilisé.'];
        }

        // 3. Hachage et Création
        $hash = password_hash($data['password'], PASSWORD_DEFAULT);

        $created = $this->writeRepo->create([
            'prenom' => $data['prenom'],
            'nom' => $data['nom'],
            'email' => $data['email'],
            'password_hash' => $hash,
            'sexe' => $data['sexe'],
            'specialite' => $data['specialite']
        ]);

        if (!$created) {
            return ['success' => false, 'error' => 'Erreur technique lors de la création du compte.'];
        }

        // 4. Token et Envoi Email
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));

        $this->verifyRepo->setVerificationToken($data['email'], $token, $expires);

        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['SERVER_NAME'] ?? 'localhost';
        $url = "$protocol://$host/verify-email?token=$token";

        $sent = $this->mailer->send(
            $data['email'],
            'Vérifiez votre adresse email - DashMed',
            'emails/verify-email',
            [
                'name' => $data['prenom'],
                'url' => $url
            ]
        );

        if (!$sent) {
            return ['success' => true, 'warning' => 'Compte créé, mais échec de l\'envoi du mail.'];
        }

        return ['success' => true];
    }
}