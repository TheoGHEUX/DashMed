<?php
namespace Controllers;

/**
 * Contrôleur pour la page d'accueil (après connexion)
 * 
 */
final class AccueilController {
    /**
     * Affiche la page d'accueil pour les utilisateurs connectés
     * 
     * @return void
     */
    public function index(): void {
        if (empty($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }
        require __DIR__ . '/../Views/accueil.php';
    }
}
