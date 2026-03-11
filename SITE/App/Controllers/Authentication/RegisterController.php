<?php

declare(strict_types=1);

namespace App\Controllers\Authentication;

use Core\Controller\AbstractController;
use Core\Security\RateLimiter;
use App\Models\Doctor\Factories\DoctorUseCaseFactory;
use App\Models\Doctor\Enums\Specialite;

/**
 * Contrôleur d'inscription
 * Gère l'affichage du formulaire et le traitement de l'inscription
 */
final class RegisterController extends AbstractController
{
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

    public function register(): void
    {
        $this->startSession();

        // Protection contre le spam d'inscriptions
        if (RateLimiter::isBlocked('register_attempts', 5, 3600)) {
            $this->render('authentication/register', [
                'errors' => ['Trop de tentatives. Veuillez réessayer dans 1 heure.'],
                'old' => [],
                'specialites' => Specialite::all(),
                'csrf_token' => \Core\Csrf::token()
            ]);
            return;
        }

        // Validation CSRF via la méthode standard
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
