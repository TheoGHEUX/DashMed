<?php

namespace Core;

/**
 * Gestionnaire de vues.
 *
 * Charge les templates et transmet les variables.
 *
 * @package Core
 */
final class View
{
    /**
     * Affiche une vue avec ses paramètres.
     *
     * Gère automatiquement l'erreur 404 si le fichier est introuvable.
     *
     * @param string $path   Nom du fichier (sans .php) dans le dossier Views/
     * @param array  $params Données à transmettre à la vue
     */
    public static function render(string $path, array $params = []): void
    {
        $baseDir = dirname(__DIR__) . '/Views/';
        $file = $baseDir . $path . '.php';

        if (!is_file($file)) {
            error_log("[VIEW] Vue introuvable : $file");
            http_response_code(404);

            $fallback = $baseDir . 'errors/404.php';

            if (is_file($fallback)) {
                include $fallback;
            } else {
                echo "<h1>404 - Page introuvable</h1><p>Le fichier de vue '$path' n'existe pas.</p>";
            }
            return;
        }

        if (!empty($params)) {
            extract($params, EXTR_SKIP);
        }

        include $file;
    }
}