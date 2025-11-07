<?php
namespace Controllers;

/**
 * Contrôleur : Page d'accueil
 *
 * Affiche la page d'accueil du site.
 * Méthode unique :
 *  - index(): rend la vue 'home'
 *
 * @package Controllers
 */
final class HomeController
{
    public function index(): void
    {
        // Page d'accueil (ex-index.html) rendue via View::render('home') -> fichier Views/home.php
        \View::render('home');
    }
}