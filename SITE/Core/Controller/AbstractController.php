<?php

declare(strict_types=1);

namespace Core\Controller;

use Core\View;

abstract class AbstractController
{
    use AuthTrait;
    use CsrfTrait;
    use JsonResponseTrait;

    protected function render(string $view, array $data = []): void
    {
        View::render($view, $data);
    }

    protected function redirect(string $url): void
    {
        header("Location: $url");
        exit;
    }

    protected function getPost(string $key, $default = ''): string
    {
        return trim($_POST[$key] ?? $default);
    }
}