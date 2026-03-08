<?php

declare(strict_types=1);

namespace Core\Controller;

trait AuthTrait
{
    protected function checkAuth(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        if (empty($_SESSION['user'])) {
            $this->redirect('/login');
        }
    }

    protected function getCurrentUserId(): int
    {
        return (int)($_SESSION['user']['id'] ?? 0);
    }

    protected function startSession(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    }
}