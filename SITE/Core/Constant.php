<?php

namespace Core;

/**
 * Constantes et chemins utilitaires du projet.
 *
 * Fournit des méthodes renvoyant les chemins absolus vers les dossiers clés du projet
 * (Views, Models, Core, Controllers). Centralise la configuration des chemins pour
 * éviter les chemins en dur dispersés dans le code.
 *
 *
 * @package Core
 */
final class Constant
{
    /**
     * Chemin relatif du dossier Views.
     *
     * @var string
     */
    public const VIEW_DIRECTORY = '/Views/';

    /**
     * Chemin relatif du dossier Models.
     *
     * @var string
     */
    public const MODEL_DIRECTORY = '/Models/';

    /**
     * Chemin relatif du dossier Core.
     *
     * @var string
     */
    public const CORE_DIRECTORY = '/Core/';

    /**
     * Chemin relatif du dossier Controllers.
     *
     * @var string
     */
    public const CONTROLLER_DIRECTORY = '/Controllers/';

    /**
     * Retourne le chemin absolu du dossier racine (niveau SITE).
     *
     * Ce dossier est le parent des répertoires Core, Views, Models et Controllers.
     * Utilisé comme base pour construire tous les autres chemins.
     *
     * @return string Chemin absolu du répertoire racine du projet (ex: /var/www/dashmed/SITE)
     */
    public static function rootDirectory(): string
    {
        return realpath(__DIR__ . '/../'); // __DIR__ correspond au dossier contenant cette classe
    }

    /**
     * Retourne le chemin absolu du dossier Core.
     *
     * @return string Chemin absolu vers SITE/Core/
     */
    public static function coreDirectory(): string
    {
        return self::rootDirectory() . self::CORE_DIRECTORY;
    }

    /**
     * Retourne le chemin absolu du dossier Views.
     *
     * Exemple :
     * ```php
     * require Constant::viewDirectory() . 'home.php';
     * ```
     *
     * @return string Chemin absolu vers SITE/Views/
     */
    public static function viewDirectory(): string
    {
        return self::rootDirectory() . self::VIEW_DIRECTORY;
    }

    /**
     * Retourne le chemin absolu du dossier Models.
     *
     * @return string Chemin absolu vers SITE/Models/
     */
    public static function modelDirectory(): string
    {
        return self::rootDirectory() . self::MODEL_DIRECTORY;
    }

    /**
     * Retourne le chemin absolu du dossier Controllers.
     *
     * @return string Chemin absolu vers SITE/Controllers/
     */
    public static function controllerDirectory(): string
    {
        return self::rootDirectory() . self::CONTROLLER_DIRECTORY;
    }
}