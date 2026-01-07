<?php

namespace Core;

require __DIR__ . '/Constant.php';

/**
 * Gestionnaire d'autoloading pour le projet.
 *
 * Enregistre plusieurs autoloaders ciblant les dossiers Core, Models, Views et Controllers.
 */
final class AutoLoader
{
    /**
     * Charge une classe située dans le dossier Core.
     *
     * @param string $className Nom complet de la classe (peut inclure le namespace 'Core\\').
     * @return void
     */
    public static function loadCore($className)
    {
        // Supporte les classes préfixées par le namespace Core\\
        if (str_contains($className, '\\')) {
            if (str_starts_with($className, 'Core\\')) {
                $className = substr($className, strlen('Core\\'));
            } else {
                return; // pas dans ce namespace
            }
        }
        $file = Constant::coreDirectory() . str_replace('\\', DIRECTORY_SEPARATOR, $className) . '.php';
        static::load($file);
    }

    /**
     * Charge une classe située dans le dossier Models.
     *
     * @param string $className Nom complet de la classe (peut inclure le namespace 'Models\\').
     * @return void
     */
    public static function loadModel($className)
    {
        if (str_contains($className, '\\')) {
            if (str_starts_with($className, 'Models\\')) {
                $className = substr($className, strlen('Models\\'));
            } else {
                return; // pas dans Models
            }
        }
        $file = Constant::modelDirectory() . str_replace('\\', DIRECTORY_SEPARATOR, $className) . '.php';
        static::load($file);
    }

    /**
     * Charge un fichier de vue.
     *
     * @param string $className Nom du fichier de vue (sans extension)
     * @return void
     */
    public static function loadView($className)
    {
        $file = Constant::viewDirectory() . "$className.php";
        static::load($file);
    }

    /**
     * Charge un contrôleur depuis le dossier Controllers.
     *
     * @param string $className Nom complet de la classe (peut inclure le namespace 'Controllers\\').
     * @return void
     */
    public static function loadController($className)
    {
        if (str_contains($className, '\\')) {
            if (str_starts_with($className, 'Controllers\\')) {
                $className = substr($className, strlen('Controllers\\'));
            } else {
                return; // pas dans Controllers
            }
        }
        $file = Constant::controllerDirectory() . str_replace('\\', DIRECTORY_SEPARATOR, $className) . '.php';
        static::load($file);
    }

    /**
     * Inclusion sécurisée d'un fichier PHP s'il est lisible.
     *
     * @param string $file Chemin du fichier à inclure
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
spl_autoload_register([AutoLoader::class, 'loadView']);
spl_autoload_register([AutoLoader::class, 'loadController']);
