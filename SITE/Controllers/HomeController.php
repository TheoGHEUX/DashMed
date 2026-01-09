<?php

namespace Controllers;

/**
 * Contrôleur de la page d'accueil publique.
 *
 * Affiche la page d'accueil du site (landing page).
 *
 * @package Controllers
 */
final class HomeController
{
    /**
     * Affiche la page d'accueil.
     *
     * @return void
     */
    public function index(): void
    {
        \Core\View::render('home');
    }
}