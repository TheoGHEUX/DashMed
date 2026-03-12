<?php

declare(strict_types=1);

namespace App\Controllers\Public;

use Core\Controller\AbstractController;

final class LegalNoticesController extends AbstractController
{
    public function show(): void
/**
 * Contrôleur des mentions légales.
 *
 * Affiche la page des mentions légales du site.
 */
    {
        $this->render('public/legal-notices');
    }
}
