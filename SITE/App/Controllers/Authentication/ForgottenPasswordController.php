<?php

declare(strict_types=1);

namespace App\Controllers\Authentication;

use Core\Controller\AbstractController;
use App\Models\Doctor\UseCases\Security\ForgottenPassword;
use App\Models\Doctor\Repositories\DoctorRepository;
use App\Models\Doctor\Repositories\SecurityRepository;
use App\Models\Doctor\Validators\DoctorValidator;
use Core\Services\MailerService;

final class ForgottenPasswordController extends AbstractController
{
    private ForgottenPassword $useCase;

    public function __construct()
    {
        $doctorRepo = new DoctorRepository();
        $securityRepo = new SecurityRepository();
        $validator = new DoctorValidator();
        $mailer = new MailerService();
        $this->useCase = new ForgottenPassword($doctorRepo, $securityRepo, $validator, $mailer);
    }

    public function show(): void
    {
        $this->render('Authentication/forgotten-password', [
            'errors' => [],
            'success' => '',
            'old' => []
        ]);
    }

    public function submit(): void
    {
        $this->startSession();

        // ===== Ratelimit désactivé pour DEV =====
        /*
        if (RateLimiter::isBlocked('forgot_password_attempts', 5, 3600)) {
            $this->render('Authentication/forgotten-password', [
                'errors' => ["Trop de tentatives récentes. Veuillez patienter une heure avant de réessayer."],
                'success' => '',
                'old' => []
            ]);
            return;
        }
        */
        // Fin du bloc ratelimit

        if (!$this->validateCsrf()) {
            // RateLimiter::recordAttempt('forgot_password_attempts');
            $this->render('Authentication/forgotten-password', [
                'errors' => ['Session expirée.'],
                'success' => '',
                'old' => []
            ]);
            return;
        }

        $email = trim($this->getPost('email'));

        // Validation email vide ou mauvais format
        if (empty($email)) {
            // RateLimiter::recordAttempt('forgot_password_attempts');
            $this->render('Authentication/forgotten-password', [
                'errors' => ["Veuillez renseigner une adresse email."],
                'success' => '',
                'old' => []
            ]);
            return;
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // RateLimiter::recordAttempt('forgot_password_attempts');
            $this->render('Authentication/forgotten-password', [
                'errors' => ["Veuillez saisir une adresse email valide."],
                'success' => '',
                'old' => ['email' => $email]
            ]);
            return;
        }

        $this->useCase->execute($email);
        // RateLimiter::recordAttempt('forgot_password_attempts');

        $this->render('Authentication/forgotten-password', [
            'errors' => [],
            'success' => "Si un compte existe à cette adresse, un email de réinitialisation a été envoyé.",
            'old' => []
        ]);
    }
}