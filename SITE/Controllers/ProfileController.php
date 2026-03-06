<?php

namespace Controllers;

use Core\View;

/**
 * Profil utilisateur
 *
 * Affiche les informations du compte pour un utilisateur authentifié.
 *
 * @package Controllers
 */
final class ProfileController
{
    /**
     * Affiche la page du profil.
     *
     * @return void
     */
    public function show(): void
    {
        // 1. Sécurité Session
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        // 2. Vérification Connexion
        if (empty($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        // 3. Récupération des données (depuis la session pour l'instant)
        $user = $_SESSION['user'];

        View::render('connected/profile', compact('user'));
    }
}