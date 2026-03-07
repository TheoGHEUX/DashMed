<?php

declare(strict_types=1);

namespace Models\Doctor\UseCases\Profile;

use Models\Doctor\Interfaces\IDoctorReadRepository;
use Models\Doctor\Interfaces\IDoctorWriteRepository;
use Models\Doctor\Interfaces\IDoctorVerificationRepository;
use Core\Mailer;

class ChangeEmail
{
    private IDoctorReadRepository $readRepo;
    private IDoctorWriteRepository $writeRepo;
    private IDoctorVerificationRepository $verifyRepo;

    public function __construct(
        IDoctorReadRepository $read,
        IDoctorWriteRepository $write,
        IDoctorVerificationRepository $verify
    ) {
        $this->readRepo = $read;
        $this->writeRepo = $write;
        $this->verifyRepo = $verify;
    }

    public function execute(int $userId, string $newEmail, string $currentPassword): array
    {
        // 1. Récupérer l'utilisateur
        $user = $this->readRepo->findById($userId);
        if (!$user) return ['success' => false, 'error' => 'Utilisateur introuvable.'];

        // 2. Vérifier mot de passe (sécurité critique)
        if (!password_verify($currentPassword, $user->getPasswordHash())) {
            return ['success' => false, 'error' => 'Mot de passe incorrect. Impossible de changer l\'email.'];
        }

        // 3. Vérifier si le nouvel email est libre
        if ($this->readRepo->emailExists($newEmail)) {
            return ['success' => false, 'error' => 'Cet email est déjà utilisé par un autre compte.'];
        }

        $oldEmail = $user->getEmail();

        // 4. Mettre à jour l'email (et passer email_verified à 0)
        if (!$this->writeRepo->updateEmail($userId, $newEmail)) {
            return ['success' => false, 'error' => 'Erreur technique.'];
        }

        // 5. Générer nouveau Token
        $token = bin2hex(random_bytes(32));
        $expires = (new \DateTime('+24 hours'))->format('Y-m-d H:i:s');
        $this->verifyRepo->setVerificationToken($newEmail, $token, $expires);

        // 6. Envoyer le mail de confirmation à la NOUVELLE adresse
        Mailer::sendEmailVerification($newEmail, $user->getPrenom(), $token);

        // 7. (Optionnel mais recommandé) Envoyer une alerte à l'ANCIENNE adresse
        // Utilisation de la fonction mail() native PHP pour faire simple ici, ou via Mailer::sendSecurityAlert()
        $subject = "Alerte de sécurité - Changement d'email DashMed";
        $message = "Bonjour,\n\nVotre adresse email a été modifiée vers : $newEmail.\nSi vous n'êtes pas à l'origine de cette action, contactez immédiatement le support.";
        mail($oldEmail, $subject, $message);

        return ['success' => true, 'message' => 'Email mis à jour. Veuillez vérifier votre nouvelle boîte de réception.'];
    }
}