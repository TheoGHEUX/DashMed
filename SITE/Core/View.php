<?php

// La classe View gère l'affichage des vues de l'application.
// Elle utilise la mise en tampon de sortie pour contrôler ce qui est affiché.

final class View
{

    // Affiche une vue : construit le chemin du fichier de la vue,
    public static function render($path, $params = array())
    {
        $file = Constant::viewDirectory() . $path . '.php';
        $viewData = $params;

        // Sécurise l'inclusion : évite les warnings si le fichier n'existe pas
        if (!is_file($file)) {
            // Log utile côté serveur
            error_log(sprintf('[VIEW] Fichier de vue introuvable: %s', $file));

            // Si une vue 404 existe, on l'affiche; sinon on affiche un message minimal
            $fallback = Constant::viewDirectory() . 'errors/404.php';

            // Garantir le bon statut HTTP
            http_response_code(404);

            ob_start();
            if (is_file($fallback)) {
                // Rendre disponibles les paramètres éventuels à la vue
                if (is_array($viewData) && !empty($viewData)) {
                    extract($viewData, EXTR_SKIP);
                }
                include $fallback;
            } else {
                // Dernier recours très simple, sans include
                echo '<!doctype html><html><head><meta charset="utf-8"><title>404</title></head><body><h1>404 - Vue introuvable</h1></body></html>';
            }
            ob_end_flush();
            return;
        }

        ob_start(); // commence la mise en tampon
        // Rendre disponibles les paramètres à la vue
        if (is_array($viewData) && !empty($viewData)) {
            extract($viewData, EXTR_SKIP);
        }
        include $file; // inclut la vue
        ob_end_flush(); // affiche et vide le tampon
    }
}
