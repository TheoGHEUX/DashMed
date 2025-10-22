<?php
namespace Controllers;

final class HomeController
{
    public function index(): void
    {
        // Page d'accueil (ex-index.html) rendue via View::render('home') -> fichier Views/home.php
        \View::render('home');
    }
}
