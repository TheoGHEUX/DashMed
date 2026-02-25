<?php

namespace Core;

/**
 * Autoloader PSR-4 Simplifié & Universel
 *
 * Remplace l'ancien système rigide.
 * Transforme automatiquement les Namespaces en chemins de fichiers.
 * Ex: Models\Entities\Patient -> SITE/Models/Entities/Patient.php
 */
final class AutoLoader
{
    public static function register(): void
    {
        spl_autoload_register([self::class, 'autoload']);
    }

    private static function autoload(string $className): void
    {
        // 1. Définir la racine "SITE" (dossier parent de Core)
        $baseDir = dirname(__DIR__) . DIRECTORY_SEPARATOR;

        // 2. Transformer le namespace (\) en chemin (/)
        $classPath = str_replace('\\', DIRECTORY_SEPARATOR, $className);

        // 3. Construire le chemin complet
        $file = $baseDir . $classPath . '.php';

        // 4. Charger si le fichier existe
        if (is_file($file)) {
            require $file;
        }
    }
}

// Lancement automatique
AutoLoader::register();
