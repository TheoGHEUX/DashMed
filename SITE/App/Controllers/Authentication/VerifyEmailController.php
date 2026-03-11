<?php

declare(strict_types=1);

namespace App\Controllers\Authentication;

use Core\Controller\AbstractController;
use Core\Security\RateLimiter;
use App\Models\Doctor\Factories\DoctorUseCaseFactory;

/**
 * Contrôleur de vérification d'email
 * Gère la validation du compte via token
 */
final class VerifyEmailController extends AbstractController
{
    public function showResend(): void
    {
        $this->startSession();

        $this->render('authentication/resend-verification', [
            'errors' => [],
            'success' => '',
            'email' => ''
        ]);
    }

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

    public function resend(): void
    {
        $this->startSession();

        if (RateLimiter::isBlocked('resend_verification_attempts', 5, 3600)) {
            $this->render('authentication/resend-verification', [
                'errors' => ['Trop de tentatives. Veuillez réessayer dans 1 heure.'],
                'success' => '',
                'email' => $this->getPost('email')
            ]);
            return;
        }

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
