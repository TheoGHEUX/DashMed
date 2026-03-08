<?php

declare(strict_types=1);

namespace App\Controllers\Authentication;

use Core\Controller\AbstractController;
use App\Models\Doctor\UseCases\Security\ResetPassword;

final class ResetPasswordController extends AbstractController
{
    private ResetPassword $useCase;

    public function __construct()
    {
        $this->useCase = new ResetPassword();
    }

    /**
     * Affiche le formulaire de nouveau mot de passe (après clic lien email)
     */
    public function show(): void
    {
        $token = $_GET['token'] ?? '';
        $email = $_GET['email'] ?? '';
        $errors = [];

        // Vérification basique que l'URL est complète
        if (!$token || !$email) {
            $errors[] = "Ce lien de réinitialisation est invalide ou incomplet.";
        }

        $this->render('authentication/reset-password', [
            'errors' => $errors,
            'success' => '',
            'token' => $token,
            'email' => $email
        ]);
    }

    /**
     * Traite le changement de mot de passe
     */
    public function submit(): void
    {
        $this->startSession();

        if (!$this->validateCsrf()) {
            $this->render('authentication/reset-password', ['errors' => ['Session expirée.']]);
            return;
        }

        $token = $this->getPost('token');
        $email = $this->getPost('email');
        $pass  = $this->getPost('password');
        $conf  = $this->getPost('password_confirm');

        if ($pass !== $conf) {
            $this->render('authentication/reset-password', [
                'errors' => ['Les mots de passe ne correspondent pas.'],
                'token' => $token,
                'email' => $email
            ]);
            return;
        }

        $result = $this->useCase->execute($email, $token, $pass);

        if ($result['success']) {
            $this->redirect('/login?reset=1');
        } else {
            $this->render('authentication/reset-password', [
                'errors' => [$result['error']],
                'token' => $token,
                'email' => $email
            ]);
        }
    }
}