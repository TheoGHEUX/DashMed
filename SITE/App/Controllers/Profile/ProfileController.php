<?php

declare(strict_types=1);

namespace App\Controllers\Profile;

use Core\Controller\AbstractController;

/**
 * Contrôleur du profil utilisateur.
 *
 * Affiche les informations du compte et permet d'accéder aux actions de modification
 * (changement d’email ou de mot de passe) pour l'utilisateur connecté.
 */
final class ProfileController extends AbstractController
{
    /**
     * Affiche la page du profil de l’utilisateur connecté.
     *
     * Si l’utilisateur n’est pas authentifié, il est redirigé vers la page de connexion.
     */
    public function show(): void
    {
        $this->checkAuth();

        $this->render('profile/profile', [
            'user' => $_SESSION['user']
        ]);
    }
}