<?php

declare(strict_types=1);

namespace App\Controllers\Public;

use Core\Controller\AbstractController;

class MapController extends AbstractController
{
    public function show(): void
    {
        $this->render('public/map');
    }
}
