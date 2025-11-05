<?php
namespace Controllers;

/**
 * Contrôleur de la page publique d'accueil.
 */
final class HomeController
{
    /**
     * Affiche la page d'accueil publique.
     *
     * @return void
     */
    public function index(): void
    {
        // Rend la vue home (Views/home.php)
        \View::render('home');
    }
}
