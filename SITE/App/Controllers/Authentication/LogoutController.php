<?php

declare(strict_types=1);

namespace App\Controllers\Authentication;

use Core\Controller\AbstractController;

/**
 * Contrôleur responsable de la déconnexion des utilisateurs.
 *
 * Ce contrôleur gère la déconnexion en sécurisant l'opération:
 * - Vérifie la validité du token CSRF
 * - Réinitialise la session et supprime le cookie
 * - Redirige l'utilisateur vers la page de connexion
 */
final class LogoutController extends AbstractController
{
    /**
     * Déconnecte l’utilisateur en nettoyant la session et en supprimant le cookie de session si nécessaire.
     *
     * Si le token CSRF n'est pas valide, la déconnexion n'est pas effectuée
     * et l'utilisateur est renvoyé vers le dashboard.
     */
    public function logout(): void
    {
        $this->startSession();
        if (!$this->validateCsrf()) {
            // Si le token CSRF n'est pas bon, on retourne simplement sur le dashboard
            $this->redirect('/dashboard');
            return;
        }

        // On vide toutes les informations de session
        $_SESSION = [];

        // Si la session utilise des cookies, on supprime aussi le cookie de session
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            $sessName = session_name() ?: 'PHPSESSID';
            setcookie(
                $sessName,
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        session_destroy();

        // L'utilisateur est renvoyé vers la page de connexion
        $this->redirect('/login');
    }
}