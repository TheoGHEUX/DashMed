<?php

namespace Core;

/**
 * Gestionnaire d'affichage des vues
 *
 * Utilise la mise en tampon de sortie pour inclure les fichiers de vue
 * et permet de transmettre des variables via extract() à la vue.
 *
 * @package Core
 */
final class View
{
    /**
     * Rend une vue PHP en lui passant des paramètres.
     *
     * Processus :
     * - Construit le chemin complet vers Views/{$path}. php
     * - Si le fichier n'existe pas, affiche la vue `errors/404.php` (HTTP 404)
     * - Sinon, extrait les paramètres comme variables et inclut la vue
     *
     * Les paramètres sont extraits avec EXTR_SKIP pour éviter l'écrasement de
     * variables existantes.
     *
     * @param string $path   Chemin relatif de la vue (depuis `Views/`), sans extension
     * @param array $params  Variables à rendre disponibles dans la vue
     * @return void
     */
    public static function render($path, $params = array())
    {
        $file = Constant::viewDirectory() . $path . '.php';
        $viewData = $params;

        if (!is_file($file)) {
            error_log(sprintf('[VIEW] Fichier de vue introuvable: %s', $file));

            $fallback = Constant::viewDirectory() . 'errors/404.php';
            http_response_code(404);

            ob_start();
            if (is_file($fallback)) {
                if (is_array($viewData) && !empty($viewData)) {
                    extract($viewData, EXTR_SKIP);
                }
                include $fallback;
            } else {
                echo '<!doctype html><html><head><meta charset="utf-8"><title>404</title></head>'
                    . '<body><h1>404 - Vue introuvable</h1></body></html>';
            }
            ob_end_flush();
            return;
        }

        ob_start();
        if (is_array($viewData) && !empty($viewData)) {
            extract($viewData, EXTR_SKIP);
        }
        include $file;
        ob_end_flush();
    }
}
