<?php

declare(strict_types=1);

namespace Core\Controller;

/**
 * Trait pour l’authentification et gestion de session utilisateur côté contrôleur.
 */
trait AuthTrait
{
    /**
     * Vérifie la présence d’une session active et d’un utilisateur connecté,
     * sinon redirige vers /login.
     */
    protected function checkAuth(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        if (empty($_SESSION['user'])) {
            $this->redirect('/login');
        }
    }

    /**
     * Retourne l’ID de l’utilisateur actuellement connecté en session.
     */
    protected function getCurrentUserId(): int
    {
        return (int)($_SESSION['user']['id'] ?? 0);
    }

    /**
     * Démarre la session si elle n’est pas déjà active.
     */
    protected function startSession(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }
}