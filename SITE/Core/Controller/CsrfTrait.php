<?php

declare(strict_types=1);

namespace Core\Controller;

use Core\Csrf;

trait CsrfTrait
{
    protected function validateCsrf(): bool
    {
        return Csrf::validate($_POST['csrf_token'] ?? '');
    }

    protected function validateApiCsrf(): bool
    {
        // Headers pour API JSON
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        return $token && Csrf::validateWithoutConsuming($token);
    }
}