<?php

declare(strict_types=1);

namespace App\Controllers\Public;

use Core\Controller\AbstractController;

final class MapController extends AbstractController
{
    public function show(): void
/**
 * Contrôleur de la carte interactive publique.
 *
 * Affiche la carte des établissements ou zones de santé.
 */
    {
        $this->render('public/map');
    }
}
