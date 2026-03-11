<?php

declare(strict_types=1);

namespace App\Controllers\Public;

use Core\Controller\AbstractController;

final class LegalNoticesController extends AbstractController
{
    public function show(): void
    {
        // Utilise la nouvelle méthode render() de l'AbstractController
        $this->render('public/legal-notices');
    }
}
