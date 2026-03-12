<?php

declare(strict_types=1);

namespace App\Controllers\Authentication;

use Core\Controller\AbstractController;
use Core\Security\RateLimiter;
use App\Models\Doctor\Factories\DoctorUseCaseFactory;

/**
 * Contrôleur dédié à la validation et à la vérification d’adresse email.
 *
 * Permet de :
 * - Vérifier le compte d’un utilisateur à l’aide d’un token reçu par email
 * - Renvoyer un email de validation
 */
final class VerifyEmailController extends AbstractController
{
    /**
     * Affiche le formulaire permettant de redemander l’envoi du mail de vérification.
     */
    public function showResend(): void
    {
        $this->startSession();

        $this->render('authentication/resend-verification', [
            'errors' => [],
            'success' => '',
            'email' => ''
        ]);
    }

    /**
     * Valide le compte d’un utilisateur via le token passé en GET.
     *
     * Appelle le use case de vérification d’email.
     * Affiche un message d’erreur ou de succès selon le résultat.
     */
    public function verify(): void
    {
        $token = $_GET['token'] ?? '';
        $useCase = DoctorUseCaseFactory::createVerifyEmail();
        $result = $useCase->execute($token);

        $this->render('authentication/verify-email', [
            'errors' => isset($result['error']) ? [$result['error']] : [],
            'success' => $result['message'] ?? '',
        ]);
    }

    /**
     * Traite la demande de renvoi d’email de vérification.
     *
     * - Protège contre le ddos via RateLimiter (max 5 par heure).
     * - Vérifie le token CSRF du formulaire.
     * - Vérifie le format de l’email puis transmet la demande au use case.
     */
    public function resend(): void
    {
        $this->startSession();

        // Protection contre l’abus de demandes répétées
        if (RateLimiter::isBlocked('resend_verification_attempts', 5, 3600)) {
            $this->render('authentication/resend-verification', [
                'errors' => ['Trop de tentatives. Veuillez réessayer dans 1 heure.'],
                'success' => '',
                'email' => $this->getPost('email')
            ]);
            return;
        }

        // Vérification du token CSRF (sécurité formulaire)
        if (!$this->validateCsrf()) {
            RateLimiter::recordAttempt('resend_verification_attempts');
            $this->render('authentication/resend-verification', [
                'errors' => ['Session expirée. Veuillez recharger la page.'],
                'success' => '',
                'email' => $this->getPost('email')
            ]);
            return;
        }

        $email = $this->getPost('email');
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            RateLimiter::recordAttempt('resend_verification_attempts');
            $this->render('authentication/resend-verification', [
                'errors' => ['Veuillez saisir une adresse email valide.'],
                'success' => '',
                'email' => $email
            ]);
            return;
        }

        // Relance l’envoi du mail via le use case
        $useCase = DoctorUseCaseFactory::createResendVerificationEmail();
        $result = $useCase->execute($email);
        RateLimiter::recordAttempt('resend_verification_attempts');

        $this->render('authentication/resend-verification', [
            'errors' => isset($result['error']) ? [$result['error']] : [],
            'success' => $result['message'] ?? '',
            'email' => $email
        ]);
    }
}