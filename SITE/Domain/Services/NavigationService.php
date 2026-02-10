<?php

namespace Domain\Services;

class NavigationService
{
    private string $currentPath;

    public function __construct()
    {
        // Récupère l'URL actuelle proprement
        $this->currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    }

    /**
     * Vérifie si le lien correspond à la page actuelle
     */
    public function isActive(string $path): bool
    {
        return $this->currentPath === $path;
    }

    /**
     * Retourne la classe CSS 'current' si le lien est actif
     * (C'est plus propre que de faire des if/else dans le HTML)
     */
    public function activeClass(string $path): string
    {
        // Gère aussi les cas spéciaux (ex: /mentions-legales et /legal-notices)
        if ($path === '/mentions-legales' && ($this->currentPath === '/mentions-legales' || $this->currentPath === '/legal-notices')) {
            return ' class="current"';
        }

        return $this->currentPath === $path ? ' class="current"' : '';
    }
}
