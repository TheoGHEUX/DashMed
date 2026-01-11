<?php

namespace Controllers;

/**
 * Plan du site
 *
 * Affiche la page du plan du site pour faciliter la navigation.
 * Page accessible publiquement (sans authentification).
 *
 * @package Controllers
 */
final class MapController
{
    public function show(): void
    {
        \Core\View::render('map');
    }
}
