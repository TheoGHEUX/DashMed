<?php

declare(strict_types=1);

namespace Core\Controller;

/**
 * Classe abstraite centrale pour tous les contrôleurs.
 * Regroupe les traits Auth, CSRF et JSON pour une API rapide et sécurisée.
 * Fournit un rendu de vue et du helper POST.
 */
abstract class AbstractController
{
    use AuthTrait;
    use CsrfTrait;
    use JsonResponseTrait;

    /**
     * Affiche une vue demandée, en lui passant $data.
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

    /**
     * Redirige vers une URL.
     */
    protected function redirect(string $url): void
    {
        header("Location: $url");
        exit;
    }

    /**
     * Récupère un champ du POST et le trim (avec valeur par défaut).
     */
    protected function getPost(string $key, string $default = ''): string
    {
        return trim((string) ($_POST[$key] ?? $default));
    }
}