<?php

namespace Core;

require_once __DIR__ . '/Constant.php';

/**
 * Autoloader PSR-4 Simplifié
 *
 * Charge automatiquement n'importe quelle classe en suivant son Namespace.
 * Exemple : La classe "Models\Entities\Patient" sera cherchée dans "SITE/Models/Entities/Patient.php"
 */
final class AutoLoader
{
    /**
     * Enregistre l'autoloader auprès de PHP.
     * C'est la seule méthode publique nécessaire.
     */
    public static function register(): void
    {
        spl_autoload_register([self::class, 'autoload']);
    }

    /**
     * Méthode interne qui fait le travail de chargement
     */
    private static function autoload(string $className): void
    {
        // 1. Définir le dossier racine "SITE" (dossier parent de Core)
        $baseDir = dirname(__DIR__) . DIRECTORY_SEPARATOR;

        // 2. Transformer le namespace en chemin de fichier
        // Exemple : "Models\Repositories\UserRepo" -> "Models/Repositories/UserRepo"
        $classPath = str_replace('\\', DIRECTORY_SEPARATOR, $className);

        // 3. Construire le chemin complet
        $file = $baseDir . $classPath . '.php';

        // 4. Charger le fichier s'il existe
        if (is_file($file)) {
            require $file;
        }
    }
}