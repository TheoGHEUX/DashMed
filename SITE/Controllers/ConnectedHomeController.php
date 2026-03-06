<?php

namespace Controllers;

use Core\View;

/**
 * Page d'accueil connectée
 *
 * Affiche le tableau de bord principal pour les utilisateurs connectés.
 *
 * @package Controllers
 */
final class ConnectedHomeController
{
    /**
     * Affiche la page d'accueil.
     *
     * @return void
     */
    public function index(): void
    {
        // 1. Sécurité : On s'assure que la session est démarrée
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        // 2. Vérification : L'utilisateur est-il connecté ?
        if (empty($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        // 3. Rendu de la vue
        View::render('connected/home');
    }
}