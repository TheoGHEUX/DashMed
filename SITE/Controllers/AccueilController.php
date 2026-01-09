<?php

namespace Controllers;

/**
 * Contrôleur de la page d'accueil authentifiée.
 *
 * Affiche la page d'accueil pour les utilisateurs connectés après login.
 * Redirige vers /login si l'utilisateur n'est pas authentifié.
 *
 * @package Controllers
 */
final class AccueilController
{
    /**
     * Affiche la page d'accueil pour les utilisateurs connectés.
     *
     * Vérifie la présence de $_SESSION['user'] avant d'afficher la vue.
     * Redirige vers /login si la session est vide.
     *
     * @return void
     */
    public function index(): void
    {
        if (empty($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }
        require __DIR__ . '/../Views/accueil.php';
    }
}
