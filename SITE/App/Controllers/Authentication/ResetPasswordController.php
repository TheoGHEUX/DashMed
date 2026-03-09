<?php

declare(strict_types=1);

namespace App\Controllers\Authentication;

use Core\Controller\AbstractController;
use Core\Security\RateLimiter;
use App\Models\Doctor\UseCases\Security\ResetPassword;
use App\Models\Doctor\Repositories\DoctorRepository;
use App\Models\Doctor\Repositories\SecurityReadRepository;
use App\Models\Doctor\Repositories\SecurityWriteRepository;
use App\Models\Doctor\Validators\DoctorValidator;

final class ResetPasswordController extends AbstractController
{
    private ResetPassword $useCase;

    public function __construct()
    {
        $doctorRepo = new DoctorRepository();
        $securityRead = new SecurityReadRepository();
        $securityWrite = new SecurityWriteRepository();
        $validator = new DoctorValidator();
        $this->useCase = new ResetPassword($doctorRepo, $securityRead, $securityWrite, $validator);
    }

    public function show(): void
    {
        $this->render('Authentication/reset-password', [
            'errors' => [],
            'success' => '',
            'email' => $_GET['email'] ?? '',
            'token' => $_GET['token'] ?? ''
        ]);
    }

    public function submit(): void
    {
        $this->startSession();

        // Limite à 5 tentatives par 15 minutes
        if (RateLimiter::isBlocked('reset_password_attempts', 5, 900)) {
            $this->render('Authentication/reset-password', [
                'errors' => ['Trop de tentatives récentes. Veuillez patienter 15 minutes avant de réessayer.'],
                'success' => '',
                'email' => $this->getPost('email'),
                'token' => $this->getPost('token')
            ]);
            return;
        }

        if (!$this->validateCsrf()) {
            RateLimiter::recordAttempt('reset_password_attempts');
            $this->render('Authentication/reset-password', [
                'errors' => ['Votre session a expiré. Merci de recharger la page et de réessayer.'],
                'success' => '',
                'email' => $this->getPost('email'),
                'token' => $this->getPost('token')
            ]);
            return;
        }

        $email = $this->getPost('email');
        $token = $this->getPost('token');
        $password = $this->getPost('password');
        $confirm  = $this->getPost('password_confirm');

        $errors = [];

        // Vérif champs vides avant use-case
        if (empty($password) || empty($confirm)) {
            $errors[] = "Veuillez renseigner et confirmer votre nouveau mot de passe.";
        } elseif ($password !== $confirm) {
            $errors[] = "La confirmation du mot de passe ne correspond pas.";
        }

        if (!empty($errors)) {
            RateLimiter::recordAttempt('reset_password_attempts');
            $this->render('Authentication/reset-password', [
                'errors' => $errors,
                'success' => '',
                'email' => $email,
                'token' => $token
            ]);
            return;
        }

        // Appelle le use-case métier
        $result = $this->useCase->execute($email, $token, $password);

        if ($result['success']) {
            RateLimiter::clear('reset_password_attempts');
            $this->redirect('/login?reset=1');
        } else {
            RateLimiter::recordAttempt('reset_password_attempts');
            $errs = [];
            if (isset($result['error'])) {
                $errs = explode("\n", $result['error']);
            } else {
                $errs[] = 'Une erreur inconnue est survenue. Veuillez réessayer.';
            }

            $this->render('Authentication/reset-password', [
                'errors' => $errs,
                'success' => '',
                'email' => $email,
                'token' => $token
            ]);
        }
    }
}