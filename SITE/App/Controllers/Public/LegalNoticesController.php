<?php

declare(strict_types=1);

namespace App\Controllers\Public;

use Core\Controller\AbstractController;

class LegalNoticesController extends AbstractController
{
    public function show(): void
    {
        // Utilise la nouvelle méthode render() de l'AbstractController
        $this->render('Public/legal-notices');
    }
}