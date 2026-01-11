<?php

namespace Controllers;

/**
 * Page d'accueil
 *
 * Affiche la page d'accueil publique du site (sans authentification).
 *
 * @package Controllers
 */
final class HomeController
{
    public function index(): void
    {
        // Page d'accueil (ex-index.html) rendue via View::render('home') -> fichier Views/home.php
        \Core\View::render('home');
    }
}
