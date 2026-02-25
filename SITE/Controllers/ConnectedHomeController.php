<?php

namespace Controllers;

/**
 * Page d'accueil connectée
 *
 * Affiche la page d'accueil pour les utilisateurs connectés.
 *
 * @package Controllers
 */
final class ConnectedHomeController
{
    /**
     * Affiche la page d'accueil pour les utilisateurs connectés.
     *
     * Vérifie la présence de $_SESSION['user'] avant d'afficher la vue.
     *
     * Redirige vers la page de connexion (/login) si la session est vide.
     *
     * @return void
     */
    public function index(): void
    {
        if (empty($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }
        \Core\View::render('connected/home');
    }
}
