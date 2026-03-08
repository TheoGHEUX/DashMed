<?php

declare(strict_types=1);

namespace Core\Controller;

abstract class AbstractController
{
    use AuthTrait;
    use CsrfTrait;
    use JsonResponseTrait;

    /**
     * Affiche une vue.
     * Remplace l'ancienne classe View::render().
     */
    protected function render(string $viewPath, array $data = []): void
    {
        if (!empty($data)) {
            extract($data);
        }

        $file = dirname(__DIR__, 2) . '/App/Views/' . $viewPath . '.php';

        if (file_exists($file)) {
            require $file;
        } else {
            die("Erreur critique : La vue '$viewPath' est introuvable dans '$file'.");
        }
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