<?php

declare(strict_types=1);

namespace App\Controllers\Profile;

use Core\Controller\AbstractController;
use Core\Security\RateLimiter;
use App\Models\Doctor\Factories\DoctorUseCaseFactory;

/**
 * Contrôleur responsable du changement de mot de passe pour l’utilisateur connecté.
 *
 * - Affiche le formulaire de changement de mot de passe.
 * - Protège contre les attaques par force brute (limitation d’essais) et vérifie le token CSRF.
 */
final class ChangePasswordController extends AbstractController
{
    /**
     * Affiche le formulaire pour modifier le mot de passe.
     */
    public function showForm(): void
    {
        $this->render('profile/change-password', [
            'errors' => [],
            'success' => ''
        ]);
    }

    /**
     * Traite la demande de changement de mot de passe.
     *
     * - Limite les tentatives pour éviter les attaques par force brute.
     * - Vérifie la validité du token CSRF.
     * - Vérifie que l’utilisateur est bien connecté.
     * - Passe les données au use case de changement de mot de passe.
     * - Affiche le résultat (succès ou erreurs) à l’utilisateur.
     */
    public function submit(): void
    {
        $this->startSession();

        // Si trop de tentatives récentes, bloque temporairement
        if (RateLimiter::isBlocked('change_password_attempts', 5, 900)) {
            $this->render('profile/change-password', [
                'errors' => ['Trop de tentatives. Veuillez réessayer dans 15 minutes.'],
                'success' => ''
            ]);
            return;
        }

        // Vérifie le token CSRF
        if (!$this->validateCsrf()) {
            RateLimiter::recordAttempt('change_password_attempts');
            $this->render('profile/change-password', [
                'errors' => ['Session expirée.'],
                'success' => ''
            ]);
            return;
        }

        // Vérifie que l’utilisateur est connecté
        if (empty($_SESSION['user'])) {
            $this->redirect('/login');
            return;
        }

        $userId = $_SESSION['user']['id'];
        $oldPassword = $this->getPost('old_password');
        $newPassword = $this->getPost('password');
        $confirmPassword = $this->getPost('password_confirm');

        $useCase = DoctorUseCaseFactory::createChangePassword();
        $result = $useCase->execute($userId, $oldPassword, $newPassword, $confirmPassword);

        if ($result['success']) {
            RateLimiter::clear('change_password_attempts');
            $this->render('profile/change-password', [
                'errors' => [],
                'success' => $result['message']
            ]);
        } else {
            RateLimiter::recordAttempt('change_password_attempts');
            $this->render('profile/change-password', [
                'errors' => [$result['error'] ?? 'Erreur inconnue'],
                'success' => ''
            ]);
        }
    }
}