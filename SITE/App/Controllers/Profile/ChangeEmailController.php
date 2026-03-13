<?php

declare(strict_types=1);

namespace App\Controllers\Profile;

use Core\Controller\AbstractController;
use Core\Security\RateLimiter;
use App\Models\Doctor\Factories\DoctorUseCaseFactory;

/**
 * Contrôleur gérant le changement d’adresse email d’un médecin.
 *
 * Permet d’accéder au formulaire de changement d’email et de réaliser l’opération:
 * - Protège contre les tentatives répétées (RateLimiteur)
 * - Vérifie le token CSRF pour des raisons de sécurité
 * - Vérifie le mot de passe actuel pour confirmer l’identité
 */
final class ChangeEmailController extends AbstractController
{
    /**
     * Affiche le formulaire de changement d’email.
     */
    public function showForm(): void
    {
        $this->render('profile/change-email', [
            'errors' => [],
            'success' => ''
        ]);
    }

    /**
     * Traite la demande de changement d’adresse email.
     *
     * - Vérifie le rate limiter (5 essais max/15 min)
     * - Vérifie le token CSRF
     * - Vérifie que la session utilisateur est active
     * - Appelle le use case avec l’identifiant utilisateur, le mot de passe actuel et le nouvel email
     * - Affiche le message de réussite ou d’erreur selon le cas
     */
    public function submit(): void
    {
        $this->startSession();

        // Blocage temporaire si trop d’essais récents
        if (RateLimiter::isBlocked('change_email_attempts', 5, 900)) {
            $this->render('profile/change-email', [
                'errors' => ['Trop de tentatives. Veuillez réessayer dans 15 minutes.'],
                'success' => ''
            ]);
            return;
        }

        // Vérifie le token CSRF
        if (!$this->validateCsrf()) {
            RateLimiter::recordAttempt('change_email_attempts');
            $this->render('profile/change-email', [
                'errors' => ['Session expirée.'],
                'success' => ''
            ]);
            return;
        }

        // Vérifie que l’utilisateur est bien connecté
        if (empty($_SESSION['user'])) {
            $this->redirect('/login');
            return;
        }

        $userId = $_SESSION['user']['id'];
        $currentPassword = $this->getPost('current_password');
        $newEmail = $this->getPost('new_email');

        $useCase = DoctorUseCaseFactory::createChangeEmail();
        $result = $useCase->execute($userId, $currentPassword, $newEmail);

        if ($result['success']) {
            RateLimiter::clear('change_email_attempts');
            $this->render('profile/change-email', [
                'errors' => [],
                'success' => $result['message']
            ]);
        } else {
            RateLimiter::recordAttempt('change_email_attempts');
            $this->render('profile/change-email', [
                'errors' => [$result['error'] ?? 'Erreur inconnue'],
                'success' => ''
            ]);
        }
    }
}