<?php

declare(strict_types=1);

namespace App\Controllers\Authentication;

use Core\Controller\AbstractController;
use App\Models\Doctor\Factories\DoctorUseCaseFactory;

/**
 * Contrôleur de vérification d'email
 * Gère la validation du compte via token
 */
final class VerifyEmailController extends AbstractController
{
    public function verify(): void
    {
        $token = $_GET['token'] ?? '';
        $useCase = DoctorUseCaseFactory::createVerifyEmail();
        $result = $useCase->execute($token);

        $this->render('Authentication/verify-email', [
            'errors' => isset($result['error']) ? [$result['error']] : [],
            'success' => $result['message'] ?? '',
        ]);
    }
}