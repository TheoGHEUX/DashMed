<?php

namespace Controllers;

/**
 * Contrôleur de gestion du profil utilisateur.
 *
 * Affiche la page de profil pour un utilisateur authentifié et gère les informations
 * personnelles. Nécessite une session active avec des données utilisateur valides.
 *
 * @package Controllers
 */
final class ProfileController
{
    /**
     * Affiche la page de profil de l'utilisateur connecté.
     *
     * Vérifie l'authentification, démarre la session si nécessaire, et redirige
     * vers la page de connexion si l'utilisateur n'est pas authentifié.
     * Prépare les données utilisateur (séparation prénom/nom) pour la vue.
     *
     * Variables de session requises :
     * - $_SESSION['user']['id'] :  Identifiant de l'utilisateur
     * - $_SESSION['user']['email'] : Email de l'utilisateur
     * - $_SESSION['user']['name'] : Nom complet de l'utilisateur
     *
     *
     * @return void
     */
    public function show(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
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