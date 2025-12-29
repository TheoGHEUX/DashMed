<?php

namespace Controllers;

/**
 * Contrôleur : Mentions légales
 *
 * Responsable de l'affichage de la page des mentions légales.
 * Méthode unique :
 *  - show(): rend la vue 'legal-notices'
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
