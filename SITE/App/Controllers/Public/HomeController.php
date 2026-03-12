<?php

declare(strict_types=1);

namespace App\Controllers\Public;

use Core\Controller\AbstractController;

final class HomeController extends AbstractController
{
    public function index(): void
/**
 * Page d'accueil publique du site.
 *
 * Affiche la page d'accueil pour les visiteurs non connectés.
 */
    {
        $this->render('public/home');
    }
}
