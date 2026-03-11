<?php

declare(strict_types=1);

namespace App\Controllers\Authentication;

use Core\Controller\AbstractController;
use Core\Security\RateLimiter;
use App\Models\Doctor\Factories\DoctorUseCaseFactory;

/**
 * Contrôleur de réinitialisation de mot de passe
 * Gère la réinitialisation après vérification du token
 */
final class ResetPasswordController extends AbstractController
{

    public function show(): void
    {
        $this->startSession();
        $this->render('authentication/reset-password', [
            'errors'  => [],
            'success' => '',
            'email'   => $_GET['email'] ?? '',
            'token'   => $_GET['token'] ?? '',
            'csrf_token' => \Core\Csrf::token()
        ]);
    }

    public function submit(): void
    {
        $this->startSession();

        $postedEmail = $this->getPost('email');
        $postedToken = $this->getPost('token');

        // Protection force brute
        if (RateLimiter::isBlocked('reset_password_attempts', 5, 900)) {
            $this->render('authentication/reset-password', [
                'errors' => ['Trop de tentatives. Veuillez réessayer dans 15 minutes.'],
                'success' => '',
                'email' => $postedEmail,
                'token' => $postedToken
            ]);
            return;
        }

        // Validation CSRF
        if (!$this->validateCsrf()) {
            RateLimiter::recordAttempt('reset_password_attempts');
            $this->render('authentication/reset-password', [
                'errors' => ['Session expirée. Veuillez recharger la page.'],
                'success' => '',
                'email' => $postedEmail,
                'token' => $postedToken
            ]);
            return;
        }

        $email = $this->getPost('email');
        $token = $this->getPost('token');
        $password = $this->getPost('password');
        $confirm  = $this->getPost('password_confirm');

        $errors = [];
        if (empty($password) || empty($confirm)) {
            $errors[] = "Veuillez renseigner et confirmer votre nouveau mot de passe.";
        } elseif ($password !== $confirm) {
            $errors[] = "La confirmation du mot de passe ne correspond pas.";
        }

        if (!empty($errors)) {
            $this->render('authentication/reset-password', [
                'errors' => $errors,
                'success' => '',
                'email' => $email,
                'token' => $token
            ]);
            return;
        }

        $useCase = DoctorUseCaseFactory::createResetPassword();
        $result = $useCase->execute($email, $token, $password);

        if ($result['success']) {
            RateLimiter::clear('reset_password_attempts');
            $this->render('authentication/reset-password', [
                'errors' => [],
                'success' => "Votre mot de passe a bien été réinitialisé. Vous pouvez maintenant vous connecter.",
                'email'   => '',
                'token'   => ''
            ]);
        } else {
            RateLimiter::recordAttempt('reset_password_attempts');
            $errs = [];
            if (isset($result['error']) && is_string($result['error'])) {
                $errs = explode("\n", $result['error']);
            } else {
                $errs[] = 'Une erreur inconnue est survenue. Veuillez réessayer.';
            }
            $this->render('authentication/reset-password', [
                'errors' => $errs,
                'success' => '',
                'email' => $email,
                'token' => $token
            ]);
        }
    }
}
