<?php

namespace Controllers;

use Core\View;

final class HomeController
{
    public function index(): void
    {
        View::render('home', [
            'pageTitle' => 'Accueil - DashMed'
        ]);
    }
}