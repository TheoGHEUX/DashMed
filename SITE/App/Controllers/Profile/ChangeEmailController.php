<?php

declare(strict_types=1);

namespace App\Controllers\Profile;

use Core\Controller\AbstractController;
use Core\Services\MailerService;
use Models\Doctor\Repositories\DoctorReadRepository;
use Models\Doctor\Repositories\DoctorWriteRepository;
use Models\Doctor\Repositories\DoctorVerificationRepository;
use Models\Doctor\UseCases\Profile\ChangeEmail;

final class ChangeEmailController extends AbstractController
{
    private ChangeEmail $useCase;

    public function __construct()
    {
        $this->useCase = new ChangeEmail(
            new DoctorReadRepository(),
            new DoctorWriteRepository(),
            new DoctorVerificationRepository(),
            new MailerService()
        );
    }

    public function show(): void
    {
        $this->checkAuth();
        $this->render('auth/change-email', ['errors' => [], 'success' => '']);
    }

    public function submit(): void
    {
        $this->checkAuth();

        if (!$this->validateCsrf()) {
            $this->render('auth/change-email', ['errors' => ['Session expirée.']]);
            return;
        }

        $currentPassword = $this->getPost('current_password');
        $newEmail = $this->getPost('new_email');
        $confirmEmail = $this->getPost('new_email_confirm');

        // AJOUT : Validation de base
        if (empty($newEmail) || !filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            $this->render('auth/change-email', ['errors' => ['Format d\'email invalide.']]);
            return;
        }

        if ($newEmail !== $confirmEmail) {
            $this->render('auth/change-email', ['errors' => ['Les emails ne correspondent pas.']]);
            return;
        }

        // Exécution
        $userId = (int) $_SESSION['user']['id'];
        $result = $this->useCase->execute($userId, $currentPassword, $newEmail);

        $this->render('auth/change-email', [
            'errors' => $result['success'] ? [] : [$result['error']],
            'success' => $result['success'] ? $result['message'] : ''
        ]);
    }
}