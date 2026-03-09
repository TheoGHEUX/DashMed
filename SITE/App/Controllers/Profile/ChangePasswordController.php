<?php

declare(strict_types=1);

namespace App\Controllers\Profile;

use Core\Controller\AbstractController;
use Core\Security\RateLimiter;
use App\Models\Doctor\UseCases\Profile\ChangePassword;
use App\Models\Doctor\Repositories\DoctorRepository;
use App\Models\Doctor\Validators\DoctorValidator;

final class ChangePasswordController extends AbstractController
{
    private ChangePassword $useCase;

    public function __construct()
    {
        $doctorRepo = new DoctorRepository();
        $validator = new DoctorValidator();
        $this->useCase = new ChangePassword($doctorRepo, $validator);
    }

    public function showForm(): void
    {
        $this->render('Profile/change-password', [
            'errors' => [],
            'success' => ''
        ]);
    }

    public function submit(): void
    {
        $this->startSession();

        if (RateLimiter::isBlocked('change_password_attempts', 5, 900)) {
            $this->render('Profile/change-password', [
                'errors' => ['Trop de tentatives. Veuillez réessayer dans 15 minutes.'],
                'success' => ''
            ]);
            return;
        }
        if (!$this->validateCsrf()) {
            RateLimiter::recordAttempt('change_password_attempts');
            $this->render('Profile/change-password', [
                'errors' => ['Session expirée.'],
                'success' => ''
            ]);
            return;
        }

        if (empty($_SESSION['user'])) {
            $this->redirect('/login');
            return;
        }

        $userId = $_SESSION['user']['id'];
        $oldPassword = $this->getPost('old_password');
        $newPassword = $this->getPost('password');
        $confirmPassword = $this->getPost('password_confirm');

        $result = $this->useCase->execute($userId, $oldPassword, $newPassword, $confirmPassword);

        if ($result['success']) {
            RateLimiter::clear('change_password_attempts');
            $this->render('Profile/change-password', [
                'errors' => [],
                'success' => $result['message']
            ]);
        } else {
            RateLimiter::recordAttempt('change_password_attempts');
            $this->render('Profile/change-password', [
                'errors' => [$result['error'] ?? 'Erreur inconnue'],
                'success' => ''
            ]);
        }
    }
}