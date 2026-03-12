<?php

declare(strict_types=1);

namespace App\Models\Doctor\UseCases\Authentication;

use App\Models\Doctor\Interfaces\IDoctorVerificationRepository;

/**
 * Use case pour la validation d'email médecin.
 *
 * Un use case (cas d'usage) regroupe la logique métier pour une action précise du domaine.
 * Il orchestre les appels aux repositories, validators, etc., pour réaliser une tâche métier complète.
 */
final class VerifyEmail
{
    private IDoctorVerificationRepository $verifyRepo;

    public function __construct(IDoctorVerificationRepository $verifyRepo)
    {
        $this->verifyRepo = $verifyRepo;
    }

    public function execute(string $token): array
    {
        if (!$token) {
            return ['error' => 'Lien de confirmation invalide.'];
        }

        // Recherche le compte associé au token
        $data = $this->verifyRepo->findByVerificationToken($token);
        if (!$data) {
            return ['error' => 'Lien de confirmation invalide ou déjà utilisé.'];
        }

        // Vérifie la date d'expiration
        if (empty($data['email_verification_expires']) || $data['email_verification_expires'] < date('Y-m-d H:i:s')) {
            return ['error' => 'Ce lien de validation est expiré.'];
        }

        // Valide le compte
        $success = $this->verifyRepo->verifyEmailToken($token);
        if (!$success) {
            return ['error' => "Impossible de valider l'adresse email. Veuillez réessayer."];
        }

        return ['message' => 'Votre adresse email a bien été vérifiée, vous pouvez maintenant vous connecter.'];
    }
}
