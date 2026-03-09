<?php

declare(strict_types=1);

namespace App\Controllers\Authentication;

use Core\Controller\AbstractController;
use App\Models\Doctor\UseCases\Authentication\VerifyEmail;
use App\Models\Doctor\Repositories\DoctorVerificationRepository;

final class VerifyEmailController extends AbstractController
{
    private VerifyEmail $useCase;

    public function __construct()
    {
        $verifyRepo = new DoctorVerificationRepository();
        $this->useCase = new VerifyEmail($verifyRepo);
    }

    public function verify(): void
    {
        $token = $_GET['token'] ?? '';
        $result = $this->useCase->execute($token);

        $this->render('Authentication/verify-email', [
            'errors' => isset($result['error']) ? [$result['error']] : [],
            'success' => $result['message'] ?? '',
        ]);
    }
}