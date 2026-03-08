<?php

declare(strict_types=1);

namespace Models\Doctor\UseCases\Profile;

// Tes namespaces actuels pour les Repositories
use Models\Doctor\Interfaces\IDoctorReadRepository;
use Models\Doctor\Interfaces\IDoctorWriteRepository;
use Models\Doctor\Interfaces\IDoctorVerificationRepository;

// L'interface Mailer qui est dans App/Interfaces (le seul truc qui y est)
use App\Interfaces\IMailer;

class ChangeEmail
{
    private IDoctorReadRepository $readRepo;
    private IDoctorWriteRepository $writeRepo;
    private IDoctorVerificationRepository $verifyRepo;
    private IMailer $mailer;

    public function __construct(
        IDoctorReadRepository $read,
        IDoctorWriteRepository $write,
        IDoctorVerificationRepository $verify,
        IMailer $mailer
    ) {
        $this->readRepo = $read;
        $this->writeRepo = $write;
        $this->verifyRepo = $verify;
        $this->mailer = $mailer;
    }

    public function execute(int $userId, string $currentPassword, string $newEmail): array
    {
        // 1. Récupérer l'utilisateur
        $user = $this->readRepo->findById($userId);
        if (!$user) {
            return ['success' => false, 'error' => 'Utilisateur introuvable.'];
        }

        // 2. Vérifier le mot de passe actuel (Sécurité)
        if (!password_verify($currentPassword, $user->getPasswordHash())) {
            return ['success' => false, 'error' => 'Mot de passe incorrect.'];
        }

        // 3. Vérifier unicité email
        if ($this->readRepo->emailExists($newEmail)) {
            return ['success' => false, 'error' => 'Cet email est déjà utilisé.'];
        }

        $oldEmail = $user->getEmail();
        $prenom = $user->getPrenom();

        // 4. Mettre à jour l'email
        if (!$this->writeRepo->updateEmail($userId, $newEmail)) {
            return ['success' => false, 'error' => 'Erreur technique.'];
        }

        // 5. Token de vérification
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));
        $this->verifyRepo->setVerificationToken($newEmail, $token, $expires);

        // 6. Notification Ancien Email (Alerte Sécurité)
        $this->mailer->send(
            $oldEmail,
            'Alerte de sécurité : Changement d\'email - DashMed',
            'emails/notification-old-email', // Assure-toi que ce fichier existe dans App/Views/emails/
            ['name' => $prenom]
        );

        // 7. Validation Nouvel Email
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['SERVER_NAME'] ?? 'localhost';
        $url = "$protocol://$host/verify-email?token=$token";

        $this->mailer->send(
            $newEmail,
            'Confirmation de votre nouvelle adresse - DashMed',
            'emails/verify-email',
            ['name' => $prenom, 'url' => $url]
        );

        return ['success' => true, 'message' => 'Adresse mise à jour. Vérifiez votre nouvelle boîte mail.'];
    }
}