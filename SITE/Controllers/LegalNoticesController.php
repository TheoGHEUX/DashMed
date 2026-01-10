<?php

namespace Controllers;

/**
 * Contrôleur : Mentions légales
 *
 * Affiche la page des mentions légales, informations RGPD et conditions d'utilisation.
 * Page accessible publiquement (non authentifié)
 *
 * Méthode unique :
 *   - show(): rend la vue 'legal-notices'.
 *
 * @package Controllers
 */
final class LegalNoticesController
{
    public function show(): void
    {
        \Core\View::render('legal-notices');
    }
}
