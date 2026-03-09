<?php

declare(strict_types=1);

namespace App\Controllers\Profile;

use Core\Controller\AbstractController;

/**
 * Profil utilisateur
 *
 * Affiche les informations du compte pour un utilisateur authentifié et propose
 * les actions de modification (email et mot de passe).
 *
 * @package Controllers
 */
final class ProfileController extends AbstractController
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
        $this->checkAuth();

        $this->render('profile/profile', [
            'user' => $_SESSION['user']
        ]);
    }
}
