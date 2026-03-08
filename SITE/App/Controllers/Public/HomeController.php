<?php

declare(strict_types=1);

namespace App\Controllers\Public;

use Core\Controller\AbstractController;

class HomeController extends AbstractController
{
    public function index(): void
    {
        $this->render('Public/home');
    }
}