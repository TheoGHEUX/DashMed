<?php

namespace Controllers;

/**
 * Contrôleur : Plan du site (Map)
 *
 * Responsable de l'affichage de la page du plan du site, pour faciliter la navigation.
 * Page accessible publiquement (non-authentifié).
 *
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
