<?php

declare(strict_types=1);

namespace Models\Doctor\UseCases\Authentication;

use Models\Doctor\Interfaces\IDoctorReadRepository;
use Models\Doctor\Interfaces\IDoctorWriteRepository;
use Models\Doctor\Interfaces\IDoctorVerificationRepository;
use Core\Mailer;

class RegisterDoctor
{
    private IDoctorReadRepository $readRepo;
    private IDoctorWriteRepository $writeRepo;
    private IDoctorVerificationRepository $verifyRepo;

    public function __construct(
        IDoctorReadRepository $readRepo,
        IDoctorWriteRepository $writeRepo,
        IDoctorVerificationRepository $verifyRepo
    ) {
        $this->readRepo = $readRepo;
        $this->writeRepo = $writeRepo;
        $this->verifyRepo = $verifyRepo;
    }

    public function execute(array $data): array
    {
        // 1. Vérifier si l'email existe déjà
        if ($this->readRepo->emailExists($data['email'])) {
            return ['success' => false, 'error' => 'Cet email est déjà utilisé.'];
        }

        // 2. Hasher le mot de passe
        $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);

        // 3. Créer le compte en base (compte_actif = 1 mais email_verified = 0)
        if (!$this->writeRepo->create($data)) {
            return ['success' => false, 'error' => 'Erreur technique lors de la création du compte.'];
        }

        // 4. Générer le token de vérification email
        $token = bin2hex(random_bytes(32));
        // Expire dans 24 heures
        $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));

        $this->verifyRepo->setVerificationToken($data['email'], $token, $expires);

        // 5. Envoyer l'email (via la classe Core\Mailer)
        // On suppose que $data['prenom'] existe dans le formulaire
        $nomComplet = ($data['prenom'] ?? '') . ' ' . ($data['nom'] ?? '');
        $mailSent = Mailer::sendEmailVerification($data['email'], trim($nomComplet), $token);

        if (!$mailSent) {
            return [
                'success' => true,
                'warning' => "Compte créé, mais l'envoi de l'email de confirmation a échoué. Contactez le support."
            ];
        }

        return ['success' => true];
    }
}