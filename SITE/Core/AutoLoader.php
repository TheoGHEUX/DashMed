<?php

namespace Core;

require __DIR__ . '/Constant.php';

/**
 * Moteur de chargement automatique des classes
 *
 * Objectif : Centraliser l'inclusion des fichiers en convertissant les Namespaces
 *            (Models, Views...) en chemins physiques sur le serveur.
 *
 * Enregistre plusieurs fonctions d'autoloading distinctes (via spl_autoload_register()),
 * chacune spécialisée pour un domaine précis du projet (Core, Models, Views, Controllers).
 *
 * Note : Elimine les inclusions manuelles ('require', 'include').
 *
 * @package Core
 */
final class AutoLoader
{
    /**
     * Charge une classe située dans le dossier Core.
     *
     * @param string $className Nom complet de la classe (peut inclure le namespace)
     * @return void
     */
    public static function loadCore($className)
    {
        // Supporte les classes préfixées par le namespace `Core\`
        if (str_contains($className, '\\')) {
            if (str_starts_with($className, 'Core\\')) {
                $className = substr($className, strlen('Core\\'));
            } else {
                return; // Ignore les classes d'autres namespaces
            }
        }
        $file = Constant::coreDirectory() . str_replace('\\', DIRECTORY_SEPARATOR, $className) . '.php';
        static::load($file);
    }

    /**
     * Charge une classe située dans le dossier Models.
     *
     * @param string $className Nom complet de la classe (peut inclure le namespace)
     * @return void
     */
    public static function loadModel($className)
    {
        if (str_contains($className, '\\')) {
            if (str_starts_with($className, 'Models\\')) {
                $className = substr($className, strlen('Models\\'));
            } else {
                return;
            }
        }
        $file = Constant::modelDirectory() . str_replace('\\', DIRECTORY_SEPARATOR, $className) . '.php';
        static::load($file);
    }

    /**
     * Charge une vue.
     *
     * Utilisé pour charger dynamiquement des vues depuis le dossier Views.
     *
     * Le nom de classe fourni est traité comme un nom de fichier sans extension.
     *
     * @param string $className Nom du fichier de vue (sans extension . php)
     * @return void
     */
    public static function loadView($className)
    {
        $file = Constant::viewDirectory() . "$className.php";
        static:: load($file);
    }

    /**
     * Charge un contrôleur depuis le dossier Controllers.
     *
     * @param string $className Nom complet de la classe (peut inclure le namespace)
     * @return void
     */
    public static function loadController($className)
    {
        if (str_contains($className, '\\')) {
            if (str_starts_with($className, 'Controllers\\')) {
                $className = substr($className, strlen('Controllers\\'));
            } else {
                return;
            }
        }
        $file = Constant:: controllerDirectory() . str_replace('\\', DIRECTORY_SEPARATOR, $className) . '.php';
        static::load($file);
    }

    /**
     * Inclusion sécurisée d'un fichier PHP s'il est lisible.
     *
     * Vérifie la lisibilité du fichier avant de l'inclure.
     *
     * Échoue silencieusement si le fichier n'existe pas ou n'est pas lisible.
     *
     * @param string $file Chemin absolu du fichier à inclure
     * @return void
     */
    private static function load($file)
    {
        if (is_readable($file)) {
            require $file;
        }
    }
}

// Enregistrement des autoloaders
spl_autoload_register([AutoLoader::class, 'loadCore']);
spl_autoload_register([AutoLoader::class, 'loadModel']);
spl_autoload_register([AutoLoader:: class, 'loadView']);
spl_autoload_register([AutoLoader::class, 'loadController']);
