<?php

declare(strict_types=1);

namespace Core;

/**
 * Autoloader PSR-4 Universel
 *
 * Charge automatiquement les classes en fonction de leur namespace.
 */
final class AutoLoader
{
    public static function register(): void
    {
        spl_autoload_register([self::class, 'autoload']);
    }

    private static function autoload(string $className): void
    {
        // 1. Définir la racine du projet "SITE/" (dossier parent de Core)
        $baseDir = dirname(__DIR__) . DIRECTORY_SEPARATOR;

        // 2. Normalisation du namespace
        // Transforme les antislashs (\) en séparateurs de dossier (/)
        // Ex: App\Controllers\Auth\LoginController -> App/Controllers/Auth/LoginController
        $logicalPath = str_replace('\\', DIRECTORY_SEPARATOR, $className);

        // 3. Construction du chemin final
        $file = $baseDir . $logicalPath . '.php';

        // 4. Chargement du fichier s'il existe
        if (is_file($file)) {
            require $file;
        }
    }
}

// Lancement automatique dès l'inclusion du fichier
AutoLoader::register();
