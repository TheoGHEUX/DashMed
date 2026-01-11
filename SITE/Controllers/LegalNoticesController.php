<?php

namespace Controllers;

/**
 * Mentions légales
 *
 * Affiche les mentions légales, informations RGPD et conditions d'utilisation.
 * Page accessible publiquement (sans authentification).
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
