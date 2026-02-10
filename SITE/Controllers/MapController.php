<?php

namespace Controllers;

use Core\View; // INDISPENSABLE

final class MapController
{
    public function show(): void
    {
        View::render('map', [
            'pageTitle' => 'Plan du site - DashMed'
        ]);
    }
}