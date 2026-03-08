<?php

declare(strict_types=1);

namespace App\Controllers\Authentication\ForgottenPasswordController;

use Core\Controller\AbstractController;
use Core\Services\MailerService;
use App\Models\Doctor\Repositories\DoctorReadRepository;
use App\Models\Doctor\Repositories\SecurityWriteRepository;
use App\Models\Doctor\UseCases\Security\ForgottenPassword;

final class ForgottenPasswordController extends AbstractController
{
    private ForgottenPassword $useCase;

    public function __construct()
    {
        $mailer = new MailerService();
        $readRepo = new DoctorReadRepository();
        $writeRepo = new SecurityWriteRepository();


        $this->useCase = new ForgottenPassword($readRepo, $writeRepo, $mailer);
    }

    public function show(): void
    {
        $this->render('auth/forgotten-password', [
            'errors' => [],
            'success' => '',
            'old' => ['email' => '']
        ]);
    }

    public function submit(): void
    {
        $this->startSession();
        if (!$this->validateCsrf()) return;

        $email = $this->getPost('email');

        // 3. On exécute la logique métier pure
        $this->useCase->execute($email);

        $this->render('auth/forgotten-password', [
            'errors' => [],
            'success' => 'Si ce compte existe, un email a été envoyé.',
            'old' => []
        ]);
    }
}