<?php

declare(strict_types=1);

namespace App\Controllers\Authentication;

use Core\Controller\AbstractController;
use Core\Security\RateLimiter;
use App\Models\Doctor\Factories\DoctorUseCaseFactory;
use App\Models\Doctor\Enums\Specialite;

/**
 * Contrôleur dédié à l’inscription d’un nouveau médecin.
 *
 * Ce contrôleur affiche le formulaire d’inscription, transmet les informations au use case dédié
 * et s’assure de la sécurité du processus
 * L’utilisateur choisit sa spécialité lors de l’inscription.
 */
final class RegisterController extends AbstractController
{
    /**
     * Affiche le formulaire d'inscription au site.
     *
     * Génère un token CSRF et récupère la liste des spécialités disponibles pour l’afficher à l’utilisateur.
     */
    public function show(): void
    {
        $this->startSession();
        $csrf_token = \Core\Csrf::token();

        $this->render('authentication/register', [
            'csrf_token' => $csrf_token,
            'errors' => [],
            'success' => '',
            'old' => [],
            'specialites' => Specialite::all()
        ]);
    }

    /**
     * Traite la soumission du formulaire d'inscription.
     *
     * - Vérifie le nombre de tentatives récentes pour limiter le spam.
     * - Valide le token CSRF.
     * - Récupère et nettoie les données envoyées par l’utilisateur.
     * - Passe les infos au use case qui gère réellement l’inscription.
     * - Réagit en cas de succès (nettoie le rate limiter et affiche un message positif) ou d’échec (affiche les erreurs).
     */
    public function register(): void
    {
        $this->startSession();

        // Protection contre le spam d'inscriptions : max 5 tentatives par heure
        if (RateLimiter::isBlocked('register_attempts', 5, 3600)) {
            $this->render('authentication/register', [
                'errors' => ['Trop de tentatives. Veuillez réessayer dans 1 heure.'],
                'old' => [],
                'specialites' => Specialite::all(),
                'csrf_token' => \Core\Csrf::token()
            ]);
            return;
        }

        // Validation du token CSRF
        if (!$this->validateCsrf()) {
            RateLimiter::recordAttempt('register_attempts');
            $this->render('authentication/register', [
                'errors' => ["Session expirée. Veuillez recharger la page et réessayer."],
                'old' => [],
                'specialites' => Specialite::all(),
                'csrf_token' => \Core\Csrf::token()
            ]);
            return;
        }

        $data = [
            'prenom'     => trim($this->getPost('prenom')),
            'nom'        => trim($this->getPost('nom')),
            'email'      => trim($this->getPost('email')),
            'password'   => $this->getPost('password'),
            'confirm'    => $this->getPost('password_confirm'),
            'specialite' => $this->getPost('specialite'),
            'sexe'       => $this->getPost('sexe')
        ];

        $useCase = DoctorUseCaseFactory::createRegisterDoctor();
        $result = $useCase->execute($data);

        if ($result['success']) {
            RateLimiter::clear('register_attempts');
            $this->render('authentication/register', [
                'success' => "Compte créé ! Un lien de vérification a été envoyé à " . htmlspecialchars($data['email']),
                'errors' => [],
                'old' => [],
                'specialites' => Specialite::all(),
                'csrf_token' => \Core\Csrf::token()
            ]);
        } else {
            RateLimiter::recordAttempt('register_attempts');
            $this->render('authentication/register', [
                'errors' => $result['errors'],
                'old' => $data,
                'specialites' => Specialite::all(),
                'csrf_token' => \Core\Csrf::token()
            ]);
        }
    }
}