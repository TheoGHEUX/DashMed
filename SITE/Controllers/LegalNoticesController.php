<?php

namespace Controllers;

/**
 * Contrôleur de la page des mentions légales.
 *
 * Affiche les mentions légales obligatoires du site.
 *
 * @package Controllers
 */
final class LegalNoticesController
{
    /**
     * Affiche la page des mentions légales.
     *
     * @return void
     */
    public function show(): void
    {
        \Core\View::render('legal-notices');
    }
}