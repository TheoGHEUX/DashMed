<?php

namespace Controllers;

use Core\View;

final class LegalNoticesController
{
    public function show(): void
    {
        View::render('legal-notices', [
            'pageTitle' => 'Mentions Légales - DashMed'
        ]);
    }
}