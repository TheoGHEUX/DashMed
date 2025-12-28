<?php

namespace Controllers;

/**
 * Contrôleur : Plan du site (Map)
 *
 * Responsable de l'affichage de la page du plan du site.
 * Méthode unique :
 *  - show(): rend la vue 'map'
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

