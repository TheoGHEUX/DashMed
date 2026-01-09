<?php

namespace Core;

require __DIR__ . '/Constant.php';

/**
 * Gestionnaire d'autoloading pour le projet.
 *
 * Enregistre plusieurs autoloaders ciblant les dossiers Core, Models, Views et Controllers.
 * Permet le chargement automatique des classes sans nécessiter de require/include manuels.
 *
 * Les autoloaders sont enregistrés automatiquement lors de l'inclusion de ce fichier.
 * Ils supportent les classes avec ou sans namespace.
 *
 *
 * @package Core
 */
final class AutoLoader
{
    /**
     * Charge une classe située dans le dossier Core.
     *
     * Supporte les noms de classes avec ou sans le namespace 'Core\'.
     * Ignore les classes qui ne font pas partie du namespace Core.
     *
     * Exemples de classes chargées :
     * - `Core\Router` → SITE/Core/Router.php
     * - `Core\Database` → SITE/Core/Database.php
     * - `Router` (sans namespace) → SITE/Core/Router.php
     *
     * @param string $className Nom complet de la classe (ex: 'Core\Router' ou 'Router')
     * @return void
     */
    public static function loadCore($className): void
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
     * Supporte les noms de classes avec ou sans le namespace 'Models\'.
     * Ignore les classes qui ne font pas partie du namespace Models.
     *
     * Exemples de classes chargées :
     * - `Models\User` → SITE/Models/User.php
     * - `Models\Patient` → SITE/Models/Patient.php
     * - `User` (sans namespace) → SITE/Models/User.php
     *
     * @param string $className Nom complet de la classe (ex: 'Models\User' ou 'User')
     * @return void
     */
    public static function loadModel($className): void
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
     * Charge un fichier de vue depuis le dossier Views.
     *
     * Note : Cette méthode n'est généralement pas utilisée pour l'autoloading classique
     * car les vues sont des templates, pas des classes.  Elle est conservée pour
     * compatibilité avec un éventuel chargement dynamique de vues.
     *
     * @param string $className Nom du fichier de vue sans extension (ex: 'home', 'profile')
     * @return void
     */
    public static function loadView($className): void
    {
        $file = Constant::viewDirectory() . "$className.php";
        static::load($file);
    }

    /**
     * Charge un contrôleur depuis le dossier Controllers.
     *
     * Supporte les noms de classes avec ou sans le namespace 'Controllers\'.
     * Ignore les classes qui ne font pas partie du namespace Controllers.
     *
     * Exemples de classes chargées :
     * - `Controllers\HomeController` → SITE/Controllers/HomeController.php
     * - `Controllers\AuthController` → SITE/Controllers/AuthController.php
     * - `HomeController` (sans namespace) → SITE/Controllers/HomeController.php
     *
     * @param string $className Nom complet de la classe (ex: 'Controllers\HomeController')
     * @return void
     */
    public static function loadController($className): void
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
     * Inclusion sécurisée d'un fichier PHP s'il existe et est lisible.
     *
     * Vérifie la lisibilité du fichier avant de l'inclure pour éviter les erreurs
     * fatales. Échoue silencieusement si le fichier n'existe pas (comportement
     * standard d'un autoloader).
     *
     * @param string $file Chemin absolu du fichier à inclure
     * @return void
     */
    private static function load($file): void
    {
        if (is_readable($file)) {
            require $file;
        }
    }
}

/**
 * Enregistrement automatique des autoloaders.
 *
 * Les autoloaders sont enregistrés dans l'ordre suivant :
 * 1. Core (classes du noyau)
 * 2. Models (entités et logique de données)
 * 3. Views (templates - rarement utilisé pour l'autoloading)
 * 4. Controllers (logique de présentation)
 *
 * Lors d'un appel à une classe non chargée, PHP parcourt ces autoloaders
 * dans l'ordre jusqu'à ce que l'un d'eux trouve et charge la classe.
 */
spl_autoload_register([AutoLoader::class, 'loadCore']);
spl_autoload_register([AutoLoader::class, 'loadModel']);
spl_autoload_register([AutoLoader::class, 'loadView']);
spl_autoload_register([AutoLoader::class, 'loadController']);