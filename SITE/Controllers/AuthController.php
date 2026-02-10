<?php

namespace Controllers;

use Core\View;
use Models\Repositories\UserRepository;

class AuthController
{
    // Affiche la page de connexion
    public function login(): void
    {
        View::render('auth/login', [
            'pageTitle' => 'Connexion - DashMed'
        ]);
    }

    // Traite le formulaire de connexion
    public function loginPost(): void
    {
        // Pour l'instant, connexion forcée pour que tu puisses tester le reste
        // A REMPLACER par ta vraie logique de vérification plus tard
        session_start();
        $_SESSION['user_id'] = 1;
        $_SESSION['user_role'] = 'medecin';
        $_SESSION['user_name'] = 'Docteur Test';

        header('Location: dashboard');
        exit;
    }

    public function logout(): void
    {
        session_destroy();
        header('Location: /');
        exit;
    }
}