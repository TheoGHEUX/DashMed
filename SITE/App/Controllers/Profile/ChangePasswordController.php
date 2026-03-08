<?php

declare(strict_types=1);

namespace App\Controllers\Profile;

use Core\Controller\AbstractController;
use App\Models\Doctor\Repositories\DoctorReadRepository;
use App\Models\Doctor\Repositories\DoctorWriteRepository;
use App\Models\Doctor\UseCases\Profile\ChangePassword;

final class ChangePasswordController extends AbstractController
{
    private ChangePassword $useCase;

    public function __construct()
    {
        // Injection des dépendances concrètes
        $readRepo = new DoctorReadRepository();
        $writeRepo = new DoctorWriteRepository();

        $this->useCase = new ChangePassword($readRepo, $writeRepo);
    }

    public function show(): void
    {
        $this->checkAuth(); // Méthode de AbstractController
        $this->render('auth/change-password', [
            'errors' => [],
            'success' => ''
        ]);
    }

    public function submit(): void
    {
        $this->checkAuth();

        if (!$this->validateCsrf()) {
            $this->render('auth/change-password', ['errors' => ['Session expirée.']]);
            return;
        }

        // Récupération sécurisée via AbstractController
        $oldPass = $this->getPost('old_password');
        $newPass = $this->getPost('password');
        $confPass = $this->getPost('password_confirm');
        $userId = (int) $_SESSION['user']['id'];

        $result = $this->useCase->execute($userId, $oldPass, $newPass, $confPass);

        $this->render('auth/change-password', [
            'errors' => $result['success'] ? [] : [$result['error']],
            'success' => $result['success'] ? $result['message'] : ''
        ]);
    }
}