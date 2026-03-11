<?php

declare(strict_types=1);

namespace App\Controllers\Authentication;

use Core\Controller\AbstractController;
use Core\Security\RateLimiter;
use App\Models\Doctor\Factories\DoctorUseCaseFactory;

/**
 * Contrôleur de mot de passe oublié
 * Gère l'envoi du lien de réinitialisation
 */
final class ForgottenPasswordController extends AbstractController
{
    public function show(): void
    {
        $this->render('authentication/forgotten-password', [
            'errors' => [],
            'success' => '',
            'old' => []
        ]);
    }

    public function submit(): void
    {
        $this->startSession();

        // Protection force brute
        if (RateLimiter::isBlocked('forgot_password_attempts', 5, 3600)) {
            $this->render('authentication/forgotten-password', [
                'errors' => ["Trop de tentatives récentes. Veuillez patienter une heure avant de réessayer."],
                'success' => '',
                'old' => []
            ]);
            return;
        }

        if (!$this->validateCsrf()) {
            RateLimiter::recordAttempt('forgot_password_attempts');
            $this->render('authentication/forgotten-password', [
                'errors' => ['Session expirée.'],
                'success' => '',
                'old' => []
            ]);
            return;
        }

        $email = trim($this->getPost('email'));

        // Validation email vide ou mauvais format
        if (empty($email)) {
            RateLimiter::recordAttempt('forgot_password_attempts');
            $this->render('authentication/forgotten-password', [
                'errors' => ["Veuillez renseigner une adresse email."],
                'success' => '',
                'old' => []
            ]);
            return;
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            RateLimiter::recordAttempt('forgot_password_attempts');
            $this->render('authentication/forgotten-password', [
                'errors' => ["Veuillez saisir une adresse email valide."],
                'success' => '',
                'old' => ['email' => $email]
            ]);
            return;
        }

        $useCase = DoctorUseCaseFactory::createForgottenPassword();
        $useCase->execute($email);
        RateLimiter::recordAttempt('forgot_password_attempts');

        $this->render('authentication/forgotten-password', [
            'errors' => [],
            'success' => "Si un compte existe à cette adresse, un email de réinitialisation a été envoyé.",
            'old' => []
        ]);
    }
}
