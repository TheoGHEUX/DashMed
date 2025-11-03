<?php
namespace Controllers;

/**
 * Contrôleur pour le vrai dashboard avec graphiques et statistiques
 * 
*/
final class DashboardController {
    /**
     * Affiche la page du tableau de bord avec graphiques et infos patients
     * 
     * @return void
     */
    public function index(): void {
        if (empty($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }
        require __DIR__ . '/../Views/dashboard.php';
    }
}
