<?php

namespace Controllers;

/**
 * Contrôleur de gestion du profil utilisateur.
 *
 * Affiche la page de profil pour un utilisateur authentifié.
 *
 * @package Controllers
 */
final class ProfileController
{
    /**
     * Affiche la page de profil de l'utilisateur connecté.
     *
     * Vérifie l'authentification et redirige vers la page de connexion si
     * l'utilisateur n'est pas connecté.  Prépare les données utilisateur
     * (séparation prénom/nom) pour la vue.
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
        $user = $_SESSION['user'];
        $parts = preg_split('/\s+/', trim($user['name'] ?? ''), 2);
        $first = $parts[0] ?? '';
        $last  = $parts[1] ?? '';
        require __DIR__ . '/../Views/profile. php';
    }
}