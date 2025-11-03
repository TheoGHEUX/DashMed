<?php

// Le but de cette classe est de centraliser les chemins des différents dossiers du projet
// De plus cette classe fournit des méthodes qui renvoient le chemin absolu vers chacun des répertoires à partir du dossier racine.
// Cela permet d'éviter d'avoir a écrire des chemins en dur dans chaque dossier du code.

final class Constant
{
    //On définie les constantes représentant les sous-repertoires du dossier

    const VIEW_DIRECTORY       = '/Views/';
    const MODEL_DIRECTORY      = '/Models/';
    const CORE_DIRECTORY       = '/Core/';
    const CONTROLLER_DIRECTORY = '/Controllers/';

    //Retourne les chemins absolu pour chaque dossier du projet
    public static function rootDirectory()
    {
        //retourne le chemin absolu du dossier racine du projet
        return realpath(__DIR__ . '/../'); // __DIR__ correspond au dossier contenat cette classe
    }

    public static function coreDirectory()
    {
        return self::rootDirectory() . self::CORE_DIRECTORY;
    }

    public static function viewDirectory()
    {
        return self::rootDirectory() . self::VIEW_DIRECTORY;
    }

    public static function modelDirectory()
    {
        return self::rootDirectory() . self::MODEL_DIRECTORY;
    }

    public static function controllerDirectory()
    {
        return self::rootDirectory() . self::CONTROLLER_DIRECTORY;
    }
}
