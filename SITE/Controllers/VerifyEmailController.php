<?php

namespace Controllers;

use Core\View;
use Domain\Interfaces\VerifyEmailUseCaseInterface;
use Domain\Interfaces\ResendVerificationEmailUseCaseInterface;

final class VerifyEmailController
{
    private VerifyEmailUseCaseInterface $verifyUseCase;
    private ResendVerificationEmailUseCaseInterface $resendUseCase;

    public function __construct(
        VerifyEmailUseCaseInterface $verifyUseCase,
        ResendVerificationEmailUseCaseInterface $resendUseCase
    ) {
        $this->verifyUseCase = $verifyUseCase;
        $this->resendUseCase = $resendUseCase;
    }

    /**
     * Action GET : Valider le lien cliqué depuis l'email.
     * Route : /verify-email?token=XYZ
     */
    public function verify(): void
    {
        $token = $_GET['token'] ?? '';

        $result = $this->verifyUseCase->execute($token);

        $success = $result['success'] ? $result['message'] : '';
        $errors = $result['errors'];

        View::render('auth/verify-email', compact('success', 'errors'));
    }

    /**
     * Action GET : Afficher le formulaire de renvoi d'email.
     * Route : /resend-verification
     */
    public function showResendForm(): void
    {
        // On prépare les variables vides pour la vue
        $success = '';
        $errors = [];
        $email = ''; // Pour pré-remplir si besoin

        View::render('auth/resend-verification', compact('success', 'errors', 'email'));
    }

    /**
     * Action POST : Traiter la demande de renvoi d'email.
     * Route : /resend-verification (POST)
     */
    public function resend(): void
    {
        $email = $_POST['email'] ?? '';

        // Appel du Use Case pour envoyer l'email
        $result = $this->resendUseCase->execute($email);

        $success = $result['success'] ? $result['message'] : '';
        $errors = $result['errors'];

        // On réaffiche la vue avec le résultat
        View::render('auth/resend-verification', compact('success', 'errors', 'email'));
    }
}