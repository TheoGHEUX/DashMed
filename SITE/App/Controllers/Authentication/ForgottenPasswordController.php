<?php

declare(strict_types=1);

namespace App\Controllers\Authentication;

use Core\Controller\AbstractController;
use Core\Security\RateLimiter;
use App\Models\Doctor\Factories\DoctorUseCaseFactory;

/**
 * Contrôleur pour la fonctionnalité "mot de passe oublié".
 *
 * Ce contrôleur permet à un utilisateur de demander un lien de réinitialisation de mot de passe.
 * Il gère l'affichage du formulaire ainsi que la logique de soumission,
 * avec protection contre la force brute et validation de l'email.
 */
final class ForgottenPasswordController extends AbstractController
{
    /**
     * Affiche le formulaire pour saisir l’adresse email de récupération.
     */
    public function show(): void
    {
        $this->render('authentication/forgotten-password', [
            'errors' => [],
            'success' => '',
            'old' => []
        ]);
    }

    /**
     * Traite la soumission du formulaire de demande de réinitialisation.
     *
     * - Vérifie que trop de tentatives n'ont pas été réalisées en peu de temps
     * - Vérifie la validité du token CSRF.
     * - Contrôle que l'email renseigné n'est pas vide et a le bon format.
     */
    public function submit(): void
    {
        $this->startSession();

        // Protection contre les attaques de type force brute
        if (RateLimiter::isBlocked('forgot_password_attempts', 5, 3600)) {
            $this->render('authentication/forgotten-password', [
                'errors' => ["Trop de tentatives récentes. Veuillez patienter une heure avant de réessayer."],
                'success' => '',
                'old' => []
            ]);
            return;
        }

        // Vérification du token CSRF
        if (!$this->validateCsrf()) {
            RateLimiter::recordAttempt('forgot_password_attempts');
            $this->render('authentication/forgotten-password', [
                'errors' => ['Session expirée.'],
                'success' => '',
                'old' => []
            ]);
            return;
        }

        $email = trim($this->getPost('email'));

        // Vérifie si le champ email est vide ou mal formaté
        if (empty($email)) {
            RateLimiter::recordAttempt('forgot_password_attempts');
            $this->render('authentication/forgotten-password', [
                'errors' => ["Veuillez renseigner une adresse email."],
                'success' => '',
                'old' => []
            ]);
            return;
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            RateLimiter::recordAttempt('forgot_password_attempts');
            $this->render('authentication/forgotten-password', [
                'errors' => ["Veuillez saisir une adresse email valide."],
                'success' => '',
                'old' => ['email' => $email]
            ]);
            return;
        }

        // Démarre la procédure d'envoi du mail de réinitialisation via le use case dédié
        $useCase = DoctorUseCaseFactory::createForgottenPassword();
        $useCase->execute($email);
        RateLimiter::recordAttempt('forgot_password_attempts');

        $this->render('authentication/forgotten-password', [
            'errors' => [],
            'success' => "Si un compte existe à cette adresse, un email de réinitialisation a été envoyé.",
            'old' => []
        ]);
    }
}