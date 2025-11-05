<?php
namespace Controllers;

/**
 * Contrôleur de la carte (page publique).
 */
final class MapController
{
    /**
     * Affiche la vue contenant la carte.
     *
     * @return void
     */
    public function show(): void
    {
        \View::render('map');
    }
}
