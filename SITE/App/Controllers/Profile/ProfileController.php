<?php

declare(strict_types=1);

namespace App\Controllers\Profile;

use Core\Controller\AbstractController;

/**
 * Contrôleur du profil utilisateur.
 *
 * Affiche les informations du compte et permet la modification de l'email ou du mot de passe.
 */
final class ProfileController extends AbstractController
{
    /**
     * Affiche la page du profil utilisateur (nécessite d'être connecté).
     */
    public function show(): void
    {
        $this->checkAuth();

        $this->render('profile/profile', [
            'user' => $_SESSION['user']
        ]);
    }
}
