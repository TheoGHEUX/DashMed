<?php
namespace Controllers;

/**
 * Contrôleur : Profil utilisateur
 *
 * Affiche la page de profil pour un utilisateur connecté.
 * Vérifie la session et redirige vers la page de connexion si l'utilisateur n'est pas authentifié.
 *
 * Comportement :
 * - Démarre la session si nécessaire
 * - Vérifie l'existence de $_SESSION['user']
 * - Prépare $user et sépare le nom complet en prénom / nom (utilisé par la vue)
 * - Inclut la vue ../Views/profile.php
 *
 * Variables utilisées dans la session :
 * - $_SESSION['user'] tableau associatif contenant au minimum : id, email, name
 *
 * @package Controllers
 */
final class ProfileController
{
    public function show(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        if (empty($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }
        $user = $_SESSION['user']; // id, email, name
        $parts = preg_split('/\s+/', trim($user['name'] ?? ''), 2);
        $first = $parts[0] ?? '';
        $last  = $parts[1] ?? '';
        require __DIR__ . '/../Views/profile.php';
    }
}