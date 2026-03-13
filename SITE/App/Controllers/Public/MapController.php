<?php

declare(strict_types=1);

namespace App\Controllers\Public;

use Core\Controller\AbstractController;

/**
 * Contrôleur pour la page de carte publique.
 */
final class MapController extends AbstractController
{
    /**
     * Affiche la page de la carte du site.
     */
    public function show(): void
    {
        $this->render('public/map');
    }
}