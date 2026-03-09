<?php

declare(strict_types=1);

namespace App\Controllers\Authentication;

use Core\Controller\AbstractController;
use Core\Security\RateLimiter;
use App\Models\Doctor\UseCases\Authentication\LoginDoctor;
use App\Models\Doctor\Repositories\DoctorRepository;

final class LoginController extends AbstractController
{
    private LoginDoctor $useCase;

    public function __construct()
    {
        $doctorRepo = new DoctorRepository();
        $this->useCase = new LoginDoctor($doctorRepo);
    }

    public function show(): void
    {
        $this->render('Authentication/login', [
            'errors' => [],
            'success' => $_GET['reset'] ?? '',
            'old' => ['email' => '']
        ]);
    }

    public function login(): void
    {
        $this->startSession();

        if (RateLimiter::isBlocked('login_attempts', 5, 900)) {
            $this->render('Authentication/login', [
                'errors' => ['Trop de tentatives. Veuillez réessayer dans 15 minutes.'],
                'old' => ['email' => $this->getPost('email')]
            ]);
            return;
        }
        if (!$this->validateCsrf()) {
            RateLimiter::recordAttempt('login_attempts');
            $this->render('Authentication/login', [
                'errors' => ['Session expirée.'],
                'old' => ['email' => $this->getPost('email')]
            ]);
            return;
        }

        try {
            $email = $this->getPost('email');
            $password = $this->getPost('password');
            $doctor = $this->useCase->execute($email, $password);

            if ($doctor) {
                RateLimiter::clear('login_attempts');
                session_regenerate_id(true);
                $_SESSION['user'] = $doctor->toSessionArray();
                $this->redirect('/dashboard');
            } else {
                RateLimiter::recordAttempt('login_attempts');
                $this->render('Authentication/login', [
                    'errors' => ['Identifiants incorrects.'],
                    'old' => ['email' => $email]
                ]);
            }
        } catch (\Exception $e) {
            RateLimiter::recordAttempt('login_attempts');
            $this->render('Authentication/login', [
                'errors' => [$e->getMessage()],
                'old' => ['email' => $this->getPost('email')]
            ]);
        }
    }
}