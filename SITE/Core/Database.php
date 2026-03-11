<?php

declare(strict_types=1);

namespace Core;

use PDO;
use PDOException;

/**
 * Gestionnaire de connexion à la base de données.
 *
 * Charge la configuration depuis le fichier .env ou les variables d'environnement.
 *
 * @package Core
 */
final class Database
{
    private static ?PDO $pdo = null;

    /**
     * Retourne l'instance PDO (Singleton).
     */
    public static function getConnection(): PDO
    {
        if (self::$pdo === null) {

            // 1. Charger les variables d'environnement
            self::loadEnv();

            // 2. Récupérer la config (Priorité : Env Var > .env file > Défaut)
            $host = self::env('DB_HOST', '127.0.0.1');
            $port = self::env('DB_PORT', '3306');
            $name = self::env('DB_NAME', 'dashmed-site_db');
            $user = self::env('DB_USER', 'root');
            $pass = self::env('DB_PASS', '');

            $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";

            try {
                self::$pdo = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false, // Sécurité : force les vraies requêtes préparées
                    PDO::ATTR_PERSISTENT         => false,
                ]);
            } catch (PDOException $e) {
                // En production, ne jamais afficher le message d'erreur brut (contient le mot de passe)
                error_log("[DB Error] " . $e->getMessage());

                // Si on est en mode debug local, on affiche, sinon message générique
                if (self::env('APP_DEBUG') === '1') {
                    die("Erreur de connexion SQL : " . $e->getMessage());
                } else {
                    die("Service momentanément indisponible.");
                }
            }
        }
        return self::$pdo;
    }

    /**
     * Récupère une variable d'environnement avec valeur par défaut.
     */
    private static function env(string $key, string $default = ''): string
    {
        // 1. Chercher dans $_ENV (peuplé par notre loader ou le serveur)
        if (isset($_ENV[$key])) {
            return (string)$_ENV[$key];
        }
        // 2. Chercher via getenv() (variables serveur/docker)
        $val = getenv($key);
        if ($val !== false) {
            return (string)$val;
        }
        return $default;
    }

    /**
     * Chargeur simpliste de fichier .env (si non chargé par le serveur).
     */
    private static function loadEnv(): void
    {
        // On ne recharge pas si DB_HOST existe déjà (évite de relire le fichier)
        if (isset($_ENV['DB_HOST']) || getenv('DB_HOST')) {
            return;
        }

        // Chemin vers le .env : 
        // Local: DashMed/.env
        // Production: /home/dashmed-site/config/.env (remonte de SITE/Core -> SITE -> DashMed -> www -> racine -> config)
        $root = dirname(__DIR__, 4); // Remonte à la racine du serveur
        $envFile = $root . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . '.env';
        
        // Si pas trouvé, essayer dans la racine du projet (environnement local)
        if (!is_file($envFile)) {
            $root = dirname(__DIR__, 2); // DashMed/
            $envFile = $root . DIRECTORY_SEPARATOR . '.env';
        }

        if (is_file($envFile) && is_readable($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if ($lines === false) return;

            foreach ($lines as $line) {
                // Ignorer les commentaires (# ou ;)
                if (str_starts_with(trim($line), '#') || str_starts_with(trim($line), ';')) {
                    continue;
                }

                // Parser CLÉ=VALEUR
                if (str_contains($line, '=')) {
                    [$key, $value] = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value);

                    // Nettoyer les guillemets éventuels
                    $value = trim($value, '"\'');

                    // Mettre dans $_ENV et putenv pour compatibilité globale
                    if (!array_key_exists($key, $_ENV)) {
                        $_ENV[$key] = $value;
                        putenv("$key=$value");
                    }
                }
            }
        }
    }
}