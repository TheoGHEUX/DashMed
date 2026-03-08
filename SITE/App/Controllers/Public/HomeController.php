<?php

declare(strict_types=1);

namespace Controllers\Public;

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
