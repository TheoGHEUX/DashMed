<?php

declare(strict_types=1);

namespace App\Controllers\Authentication;

use Core\Controller\AbstractController;
use Core\Security\RateLimiter;
use App\Models\Doctor\Factories\DoctorUseCaseFactory;

/**
 * Contrôleur de connexion
 * Gère l'affichage du formulaire et le traitement de la connexion
 */
final class LoginController extends AbstractController
{
    public function show(): void
    {
        $this->render('authentication/login', [
            'errors' => [],
            'success' => $_GET['reset'] ?? '',
            'old' => ['email' => '']
        ]);
    }

    public function login(): void
    {
        $this->startSession();

        if (RateLimiter::isBlocked('login_attempts', 5, 900)) {
            $this->render('authentication/login', [
                'errors' => ['Trop de tentatives. Veuillez réessayer dans 15 minutes.'],
                'old' => ['email' => $this->getPost('email')]
            ]);
            return;
        }
        
        if (!$this->validateCsrf()) {
            RateLimiter::recordAttempt('login_attempts');
            $this->render('authentication/login', [
                'errors' => ['Session expirée.'],
                'old' => ['email' => $this->getPost('email')]
            ]);
            return;
        }

        try {
            $useCase = DoctorUseCaseFactory::createLoginDoctor();
            $email = $this->getPost('email');
            $password = $this->getPost('password');
            $doctor = $useCase->execute($email, $password);

            if ($doctor) {
                RateLimiter::clear('login_attempts');
                session_regenerate_id(true);
                $_SESSION['user'] = $doctor->toSessionArray();
                $this->redirect('/dashboard');
            } else {
                RateLimiter::recordAttempt('login_attempts');
                $this->render('authentication/login', [
                    'errors' => ['Identifiants incorrects.'],
                    'old' => ['email' => $email]
                ]);
            }
        } catch (\Throwable $e) {
            RateLimiter::recordAttempt('login_attempts');
            error_log('[LOGIN] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            $this->render('authentication/login', [
                'errors' => ['Une erreur est survenue lors de la connexion. Veuillez réessayer.'],
                'old' => ['email' => $this->getPost('email')]
            ]);
        }
    }
}
