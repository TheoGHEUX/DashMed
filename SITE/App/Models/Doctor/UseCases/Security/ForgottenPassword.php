<?php

declare(strict_types=1);

namespace App\Models\Doctor\UseCases\Security;

use App\Interfaces\IMailer;
use App\Models\Doctor\Repositories\DoctorReadRepository;
use App\Models\Doctor\Repositories\SecurityWriteRepository;

class ForgottenPassword
{
    private DoctorReadRepository $readRepo;
    private SecurityWriteRepository $writeRepo;
    private IMailer $mailer;

    public function __construct(
        DoctorReadRepository $readRepo,
        SecurityWriteRepository $writeRepo,
        IMailer $mailer
    ) {
        $this->readRepo = $readRepo;
        $this->writeRepo = $writeRepo;
        $this->mailer = $mailer;
    }

    public function execute(string $email): void
    {
        $doctor = $this->readRepo->findByEmail($email);

        if ($doctor) {
            $token = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $token);
            $expires = date('Y-m-d H:i:s', time() + 3600);

            $this->writeRepo->createResetToken($email, $tokenHash, $expires);

            // Construction URL
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['SERVER_NAME'] ?? 'localhost';
            $url = "$protocol://$host/reset-password?token=$token&email=$email";

            $name = $doctor->getPrenom() . ' ' . $doctor->getNom();

            // Envoi via l'interface
            $this->mailer->send(
                $email,
                'Réinitialisation de mot de passe',
                'emails/reset-password',
                ['name' => $name, 'url' => $url]
            );
        }
    }
}