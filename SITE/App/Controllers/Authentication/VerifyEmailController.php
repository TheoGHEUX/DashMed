<?php

declare(strict_types=1);

namespace App\Controllers\Authentication;

use Core\Controller\AbstractController;
// On importe les implémentations concrètes uniquement ici (dans le controleur)
use App\Models\Doctor\Repositories\DoctorVerificationRepository;
// On importe le UseCase
use App\Models\Doctor\UseCases\Authentication\VerifyDoctorEmail; // Attention au nommage (VerifyEmail ou VerifyDoctorEmail)

final class VerifyEmailController extends AbstractController
{
    private VerifyDoctorEmail $useCase;

    public function __construct()
    {
        // Injection de dépendance Manuelle ("Composition Root")
        // Le Contrôleur décide quelle implémentation donner au UseCase
        $repo = new DoctorVerificationRepository();

        $this->useCase = new VerifyDoctorEmail($repo);
    }

    public function verify(): void
    {
        $token = $_GET['token'] ?? '';

        if (!$token) {
            $this->render('auth/verify-email', ['errors' => ['Token manquant.']]);
            return;
        }

        $result = $this->useCase->execute($token);

        $this->render('auth/verify-email', [
            'errors' => $result['success'] ? [] : [$result['error']],
            'success' => $result['success'] ? ($result['message'] ?? 'Email vérifié !') : ''
        ]);
    }

    public function resend(): void
    {
        $this->startSession();
        // Logique de renvoi (A faire plus tard avec un UseCase ResendEmail)
        $this->render('auth/resend-verification', ['success' => 'Fonctionnalité en cours de migration.']);
    }
}