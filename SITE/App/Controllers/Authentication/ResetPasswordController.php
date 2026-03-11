<?php

declare(strict_types=1);

namespace App\Controllers\Authentication;

use Core\Controller\AbstractController;
use App\Models\Doctor\Factories\DoctorUseCaseFactory;

/**
 * Contrôleur de réinitialisation de mot de passe
 * Gère la réinitialisation après vérification du token
 */
final class ResetPasswordController extends AbstractController
{

    public function show(): void
    {
        $this->render('Authentication/reset-password', [
            'errors'  => [],
            'success' => '',
            'email'   => $_GET['email'] ?? '',
            'token'   => $_GET['token'] ?? ''
        ]);
    }

    public function submit(): void
    {
        ini_set('display_errors', '1');
        ini_set('display_startup_errors', '1');
        error_reporting(E_ALL);

        $this->startSession();

        $email = $this->getPost('email');
        $token = $this->getPost('token');
        $password = $this->getPost('password');
        $confirm  = $this->getPost('password_confirm');

        $errors = [];
        if (empty($password) || empty($confirm)) {
            $errors[] = "Veuillez renseigner et confirmer votre nouveau mot de passe.";
        } elseif ($password !== $confirm) {
            $errors[] = "La confirmation du mot de passe ne correspond pas.";
        }

        if (!empty($errors)) {
            $this->render('Authentication/reset-password', [
                'errors' => $errors,
                'success' => '',
                'email' => $email ?? '',
                'token' => $token ?? ''
            ]);
            return;
        }

        $useCase = DoctorUseCaseFactory::createResetPassword();
        $result = $useCase->execute($email, $token, $password);

        if ($result['success']) {
            $this->render('Authentication/reset-password', [
                'errors' => [],
                'success' => "Votre mot de passe a bien été réinitialisé. Vous pouvez maintenant vous connecter.",
                'email'   => '',
                'token'   => ''
            ]);
        } else {
            $errs = [];
            if (isset($result['error'])) {
                $errs = explode("\n", $result['error']);
            } else {
                $errs[] = 'Une erreur inconnue est survenue. Veuillez réessayer.';
            }
            $this->render('Authentication/reset-password', [
                'errors' => $errs,
                'success' => '',
                'email' => $email ?? '',
                'token' => $token ?? ''
            ]);
        }
    }
}