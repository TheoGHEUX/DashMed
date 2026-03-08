<?php

declare(strict_types=1);

namespace App\Controllers\Dashboard;

use Core\Controller\AbstractController;

final class ConnectedHomeController extends AbstractController
{
    /**
     * Affiche la page d'accueil connectée.
     * Utilise checkAuth() du parent pour sécuriser l'accès.
     */
    public function index(): void
    {
        // Redirige vers /login si pas connecté
        $this->checkAuth();

        $this->render('connected/home', [
            'user' => $_SESSION['user']
        ]);
    }
}