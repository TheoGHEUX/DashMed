<?php

namespace Controllers;

/**
 * Contrôleur de la page plan du site.
 *
 * Affiche le plan de navigation du site.
 *
 * @package Controllers
 */
final class MapController
{
    /**
     * Affiche la page du plan du site.
     *
     * @return void
     */
    public function show(): void
    {
        \Core\View::render('map');
    }
}