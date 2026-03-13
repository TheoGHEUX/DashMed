<?php

declare(strict_types=1);

namespace App\Controllers\Public;

use Core\Controller\AbstractController;

/**
 * Contrôleur des mentions légales.
 */
final class LegalNoticesController extends AbstractController
{
    /**
     * Affiche la page des mentions légales.
     */
    public function show(): void
    {
        $this->render('public/legal-notices');
    }
}