<?php

// Le but de cette classe est d'enregistrer plusieurs autoloaders : chacun est associé à un répertoire du projet.
// Ainsi, lorsqu'on appellera une classe non définie, PHP essaiera de la charger en transmettant son nom à ces fonctions,
// et ce jusqu'à trouver le fichier correspondant.

//Cela permet d'eviter de definir une telle methode pour chaque dossier.
require 'Constant.php';

final class AutoLoader
{
    //On construit les chemins vers les differents repertoire du projet
    public static function loadCore($className)
    {
        // Supporte les classes avec namespace 'Core\\'
        if (str_contains($className, '\\')) {
            if (str_starts_with($className, 'Core\\')) {
                $className = substr($className, strlen('Core\\'));
            } else {
                return; // pas dans ce namespace
            }
        }
        $file = Constant::coreDirectory() . str_replace('\\', DIRECTORY_SEPARATOR, $className) . '.php';
        return static::load($file);
    }

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
        return static::load($file);
    }

    public static function loadView($className)
    {
        $file = Constant::viewDirectory() . "$className.php";
        return static::load($file);
    }

    public static function loadController($className)
    {
        // Gère namespace Controllers\\
        if (str_contains($className, '\\')) {
            if (str_starts_with($className, 'Controllers\\')) {
                $className = substr($className, strlen('Controllers\\'));
            } else {
                return; // pas dans Controllers
            }
        }
        $file = Constant::controllerDirectory() . str_replace('\\', DIRECTORY_SEPARATOR, $className) . '.php';
        return static::load($file);
    }

    private static function load($file)
    {
        //regarde si le fichier est accesible
        if (is_readable($file)) {
            require $file;
        }
    }
}

//On utilise ici les fonctions de cette classe pour charger automatiquement les classes.
// Chaque ligne enregistre un autoloader pour un fichier précis
spl_autoload_register('AutoLoader::loadCore');
spl_autoload_register('AutoLoader::loadModel');
spl_autoload_register('AutoLoader::loadView');
spl_autoload_register('AutoLoader::loadController');