<?php

namespace Core;

/**
 * Autoloader PSR-4 avec DEBUG
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

        // --- ZONE DE DÉBOGAGE ---
        // Si on cherche une classe liée à "VerifyEmail", on affiche les infos
        if (strpos($className, 'VerifyEmail') !== false) {
            echo "<div style='background: #f8d7da; color: #721c24; padding: 20px; border: 1px solid #f5c6cb; margin: 20px; font-family: monospace;'>";
            echo "<strong>🔍 DEBUG AUTOLOADER :</strong><br><br>";
            echo "<strong>Classe demandée :</strong> " . htmlspecialchars($className) . "<br>";
            echo "<strong>Chemin calculé :</strong> " . htmlspecialchars($file) . "<br>";

            if (is_file($file)) {
                echo "<strong>✅ LE FICHIER EXISTE !</strong><br>";
                echo "Si l'erreur persiste, vérifiez le <code>namespace</code> à l'intérieur du fichier.<br>";
            } else {
                echo "<strong>❌ LE FICHIER N'EXISTE PAS ICI !</strong><br>";
                echo "Vérifiez :<br>";
                echo "1. Le nom du dossier (majuscules/minuscules)<br>";
                echo "2. Le nom du fichier (orthographe exacte)<br>";
                echo "3. L'extension du fichier (.php)<br>";
            }
            echo "</div>";

            // On continue pour laisser PHP essayer de charger et afficher l'erreur fatale si besoin
        }
        // --- FIN ZONE DE DÉBOGAGE ---

        // 4. Charger si le fichier existe
        if (is_file($file)) {
            require $file;
        }
    }
}

// Lancement automatique
AutoLoader::register();