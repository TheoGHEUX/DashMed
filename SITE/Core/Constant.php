<?php

/**
 * Constantes et chemins utilitaires du projet.
 *
 * Fournit des méthodes renvoyant les chemins absolus vers les dossiers clé (Views, Models, Core, Controllers).
 */
final class Constant
{
    // Sous-répertoires relatifs au répertoire SITE
    const VIEW_DIRECTORY       = '/Views/';
    const MODEL_DIRECTORY      = '/Models/';
    const CORE_DIRECTORY       = '/Core/';
    const CONTROLLER_DIRECTORY = '/Controllers/';

    /**
     * Retourne le chemin absolu du dossier racine (niveau SITE).
     *
     * @return string Chemin absolu du répertoire racine du projet (dossier parent de Core/Views/Models/Controllers)
     */
    public static function rootDirectory()
    {
        return realpath(__DIR__ . '/../'); // __DIR__ correspond au dossier contenant cette classe
    }

    /**
     * Retourne le chemin absolu du dossier Core.
     *
     * @return string
     */
    public static function coreDirectory()
    {
        return self::rootDirectory() . self::CORE_DIRECTORY;
    }

    /**
     * Retourne le chemin absolu du dossier Views.
     *
     * @return string
     */
    public static function viewDirectory()
    {
        return self::rootDirectory() . self::VIEW_DIRECTORY;
    }

    /**
     * Retourne le chemin absolu du dossier Models.
     *
     * @return string
     */
    public static function modelDirectory()
    {
        return self::rootDirectory() . self::MODEL_DIRECTORY;
    }

    /**
     * Retourne le chemin absolu du dossier Controllers.
     *
     * @return string
     */
    public static function controllerDirectory()
    {
        return self::rootDirectory() . self::CONTROLLER_DIRECTORY;
    }
}
