<?php

declare(strict_types=1);

namespace App\Controllers\Authentication;

use Core\Controller\AbstractController;
use Core\Services\MailerService;

use App\Models\Doctor\Repositories\DoctorReadRepository;
use App\Models\Doctor\Repositories\DoctorVerificationRepository;

use App\Models\Doctor\Interfaces\IVerifyEmail;
use App\Models\Doctor\Interfaces\IResendVerificationEmail;

use App\Models\Doctor\UseCases\Authentication\VerifyEmail;
use App\Models\Doctor\UseCases\Authentication\ResendVerificationEmail;

final class VerifyEmailController extends AbstractController
{
    private IVerifyEmail $verifyUseCase;
    private IResendVerificationEmail $resendUseCase;

    public function __construct()
    {
        $verifyRepo = new DoctorVerificationRepository();
        $readRepo = new DoctorReadRepository();
        $mailer = new MailerService();

        $this->verifyUseCase = new VerifyEmail($verifyRepo);

        $this->resendUseCase = new ResendVerificationEmail(
            $readRepo,
            $verifyRepo,
            $mailer
        );
    }

    public function verify(): void
    {
        $token = $_GET['token'] ?? '';
        if (!$token) {
            $this->render('authentication/verify-email', ['errors' => ['Lien invalide.']]);
            return;
        }
        $result = $this->verifyUseCase->execute($token);
        $this->render('authentication/verify-email', [
            'errors' => $result['success'] ? [] : [$result['error']],
            'success' => $result['success'] ? ($result['message'] ?? 'Email vérifié !') : ''
        ]);
    }

    /**
     * Gère l'affichage ET la soumission du formulaire de renvoi.
     */
    public function resend(): void
    {
        // 1. Affichage du formulaire (GET)
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->render('authentication/resend-verification', [
                'success' => '',
                'errors' => [],
                'email' => ''
            ]);
            return;
        }

        // 2. Traitement du formulaire (POST)
        if (!$this->validateCsrf()) {
            $this->render('authentication/resend-verification', [
                'errors' => ['Session expirée, veuillez réessayer.'],
                'email' => $_POST['email'] ?? ''
            ]);
            return;
        }

        $email = trim($_POST['email'] ?? '');

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->render('authentication/resend-verification', [
                'errors' => ['Veuillez entrer une adresse email valide.'],
                'email' => $email
            ]);
            return;
        }

        // Appel du UseCase via l'interface
        $result = $this->resendUseCase->execute($email);

        $this->render('authentication/resend-verification', [
            'success' => $result['success'] ? $result['message'] : '',
            'errors' => $result['success'] ? [] : [$result['error']],
            'email' => $result['success'] ? '' : $email
        ]);
    }
}