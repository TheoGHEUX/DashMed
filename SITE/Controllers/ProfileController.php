<?php

namespace Controllers;

/**
 * Profil utilisateur
 *
 * Affiche les informations du compte pour un utilisateur authentifié et propose
 * les actions de modification (email et mot de passe).
 *
 * @package Controllers
 */
final class ProfileController
{
    /**
     * Affiche la page du profil.
     *
     * Vérifie l'authentification avant d'afficher la vue.
     * Redirige vers la page de connexion (/login) si l'utilisateur n'est pas connecté.
     *
     * Processus :
     * 1. Démarre la session si nécessaire
     * 2. Vérifie $_SESSION['user']
     * 3. Extrait le nom complet et le sépare en prénom/nom
     * 4. Passe $user, $first, $last à la vue profile.php
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
        require __DIR__ . '/../Views/connected/profile.php';
    }
}
