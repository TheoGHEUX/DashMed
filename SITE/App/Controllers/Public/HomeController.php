<?php

declare(strict_types=1);

namespace App\Controllers\Public;

use Core\Controller\AbstractController;

/**
 * Contrôleur pour la page d'accueil publique du site.
 */
final class HomeController extends AbstractController
{
    /**
     * Affiche la page d'accueil publique.
     */
    public function index(): void
    {
        $this->render('public/home');
    }
}