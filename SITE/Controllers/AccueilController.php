<?php

namespace Controllers;

/**
 * Contrôleur de la page d'accueil authentifiée.
 *
 * Affiche la page d'accueil réservée aux utilisateurs connectés avec
 * un lien vers le tableau de bord et une bannière de bienvenue.
 *
 * @package Controllers
 */
final class AccueilController
{
    /**
     * Affiche la page d'accueil pour les utilisateurs connectés.
     *
     * Vérifie la présence d'un utilisateur en session. Si l'utilisateur
     * n'est pas authentifié, redirige vers la page de connexion et termine
     * l'exécution.
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