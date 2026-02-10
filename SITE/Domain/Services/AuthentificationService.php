<?php

namespace Domain\Services;

class AuthenticationService
{
    /**
     * Vérifie si l'utilisateur est connecté
     */
    public function isAuthenticated(): bool
    {
        // On vérifie si la session est active et si l'utilisateur y est stocké
        return session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['user']);
    }

    /**
     * Récupère l'utilisateur connecté ou null
     */
    public function getUser(): ?array
    {
        return $this->isAuthenticated() ? $_SESSION['user'] : null;
    }

    /**
     * Redirige vers la page de login si non connecté
     */
    public function requireLogin(): void
    {
        if (!$this->isAuthenticated()) {
            header('Location: /login');
            exit;
        }
    }
}
