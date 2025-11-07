<?php
namespace Controllers;

/**
 * Contrôleur : Accueil (après connexion)
 *
 * Affiche la page d'accueil pour les utilisateurs authentifiés.
 *
 * Méthode :
 *  - index(): vérifie la session et rend la vue 'accueil'
 *
 * @package Controllers
 */
final class AccueilController {
    /**
     * Affiche la page d'accueil pour les utilisateurs connectés.
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