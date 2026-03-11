<?php

declare(strict_types=1);

namespace App\Controllers\Profile;

use Core\Controller\AbstractController;
use Core\Security\RateLimiter;
use App\Models\Doctor\Factories\DoctorUseCaseFactory;

/**
 * Contrôleur de changement d'email
 */
final class ChangeEmailController extends AbstractController
{
    public function showForm(): void
    {
        $this->render('Profile/change-email', [
            'errors' => [],
            'success' => ''
        ]);
    }

    public function submit(): void
    {
        $this->startSession();

        if (RateLimiter::isBlocked('change_email_attempts', 5, 900)) {
            $this->render('Profile/change-email', [
                'errors' => ['Trop de tentatives. Veuillez réessayer dans 15 minutes.'],
                'success' => ''
            ]);
            return;
        }
        if (!$this->validateCsrf()) {
            RateLimiter::recordAttempt('change_email_attempts');
            $this->render('Profile/change-email', [
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
        $currentPassword = $this->getPost('current_password');
        $newEmail = $this->getPost('new_email');

        $useCase = DoctorUseCaseFactory::createChangeEmail();
        $result = $useCase->execute($userId, $currentPassword, $newEmail);

        if ($result['success']) {
            RateLimiter::clear('change_email_attempts');
            $this->render('Profile/change-email', [
                'errors' => [],
                'success' => $result['message']
            ]);
        } else {
            RateLimiter::recordAttempt('change_email_attempts');
            $this->render('Profile/change-email', [
                'errors' => [$result['error'] ?? 'Erreur inconnue'],
                'success' => ''
            ]);
        }
    }
}