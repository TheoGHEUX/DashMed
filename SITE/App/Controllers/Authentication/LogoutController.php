<?php

declare(strict_types=1);

namespace App\Controllers\Authentication;

use Core\Controller\AbstractController;

final class LogoutController extends AbstractController
{
    public function logout(): void
    {
        $this->startSession();
        if (!$this->validateCsrf()) {
            $this->redirect('/dashboard');
            return;
        }

        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), '',
                time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        session_destroy();
        $this->redirect('/login');
    }
}
