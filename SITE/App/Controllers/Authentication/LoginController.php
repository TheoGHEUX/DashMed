<?php

declare(strict_types=1);

namespace App\Controllers\Authentication;

use Core\Controller\AbstractController;
use Core\Security\RateLimiter;
use App\Models\Doctor\Factories\DoctorUseCaseFactory;

/**
 * Contrôleur dédié à la connexion d’un utilisateur (médecin).
 *
 * Ce contrôleur affiche le formulaire de connexion et gère toute la logique liée à l’authentification :
 * - Protection contre la force brute avec un système de limitation d’essais.
 * - Vérification du token CSRF pour la sécurité du formulaire.
 * - Gestion des erreurs d’identifiant, de session, et des exceptions inattendues.
 */
final class LoginController extends AbstractController
{
    /**
     * Affiche le formulaire de connexion.
     * Affiche éventuellement un message de succès si la page est rechargée après une réinitialisation de mot de passe.
     */
    public function show(): void
    {
        $this->render('authentication/login', [
            'errors' => [],
            'success' => $_GET['reset'] ?? '',
            'old' => ['email' => '']
        ]);
    }

    /**
     * Gère la tentative de connexion soumise par l’utilisateur.
     *
     * Processus:
     * - Vérifie le nombre d’essais récents (contre brute force).
     * - Contrôle la validité du token CSRF.
     * - Appelle le use case de connexion pour vérifier les identifiants.
     * - En cas de succès, démarre la session utilisateur et redirige.
     * - Sinon, affiche des messages d’erreur adaptés selon le problème rencontré.
     */
    public function login(): void
    {
        $this->startSession();

        // Limite le nombre de tentatives de connexion pour éviter la force brute
        if (RateLimiter::isBlocked('login_attempts', 5, 900)) {
            $this->render('authentication/login', [
                'errors' => ['Trop de tentatives. Veuillez réessayer dans 15 minutes.'],
                'old' => ['email' => $this->getPost('email')]
            ]);
            return;
        }

        // Vérification du token CSRF
        if (!$this->validateCsrf()) {
            RateLimiter::recordAttempt('login_attempts');
            $this->render('authentication/login', [
                'errors' => ['Session expirée.'],
                'old' => ['email' => $this->getPost('email')]
            ]);
            return;
        }

        try {
            $useCase = DoctorUseCaseFactory::createLoginDoctor();
            $email = $this->getPost('email');
            $password = $this->getPost('password');
            $doctor = $useCase->execute($email, $password);

            if ($doctor) {
                RateLimiter::clear('login_attempts');
                session_regenerate_id(true); // Prévient le vol de session
                $_SESSION['user'] = $doctor->toSessionArray(); // Stocke les informations utiles en session
                $this->redirect('/dashboard');
            } else {
                RateLimiter::recordAttempt('login_attempts');
                $this->render('authentication/login', [
                    'errors' => ['Identifiants incorrects.'],
                    'old' => ['email' => $email]
                ]);
            }
        } catch (\Throwable $e) {
            RateLimiter::recordAttempt('login_attempts');
            error_log('[LOGIN] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            $this->render('authentication/login', [
                'errors' => ['Une erreur est survenue lors de la connexion. Veuillez réessayer.'],
                'old' => ['email' => $this->getPost('email')]
            ]);
        }
    }
}