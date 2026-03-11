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
            $user = self::env('DB_USER', 'root');
            $pass = self::env('DB_PASS', '');

            // Nom DB configuré (si présent), puis fallback sur les deux noms historiques
            $configuredName = self::env('DB_NAME', self::env('DB_DATABASE', ''));
            $dbNames = [];
            if ($configuredName !== '') {
                $dbNames[] = $configuredName;
            }
            $dbNames[] = 'dashmed-site_db';
            $dbNames[] = 'dashmed-site_db_2';
            $dbNames = array_values(array_unique($dbNames));

            $lastError = null;
            foreach ($dbNames as $name) {
                $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";

                try {
                    $candidate = new PDO($dsn, $user, $pass, [
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES   => false, // Sécurité : force les vraies requêtes préparées
                        PDO::ATTR_PERSISTENT         => false,
                    ]);

                    if (!self::hasRequiredTables($candidate)) {
                        error_log("[DB Error][{$name}] Schéma incomplet: tables métier manquantes.");
                        continue;
                    }

                    self::$pdo = $candidate;
                    break;
                } catch (PDOException $e) {
                    $lastError = $e;
                    error_log("[DB Error][{$name}] " . $e->getMessage());
                }
            }

            if (self::$pdo === null) {
                // Si on est en mode debug local, on affiche, sinon message générique
                if (self::env('APP_DEBUG') === '1' && $lastError instanceof PDOException) {
                    die("Erreur de connexion SQL : " . $lastError->getMessage());
                }
                die("Service momentanément indisponible.");
            }
        }
        return self::$pdo;
    }

    /**
     * Vérifie que les tables minimales nécessaires existent dans la base.
     */
    private static function hasRequiredTables(PDO $pdo): bool
    {
        $requiredTables = [
            'medecin',
            'patient',
            'suivre',
            'mesures',
            'valeurs_mesures',
            'seuil_alerte',
        ];

        foreach ($requiredTables as $table) {
            $stmt = $pdo->query("SHOW TABLES LIKE " . $pdo->quote($table));
            if ($stmt === false || $stmt->fetch() === false) {
                return false;
            }
        }

        return true;
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
            if ($lines === false) {
                return;
            }

            foreach ($lines as $line) {
                // Ignorer les commentaires (# ou ;)
                $trimmed = trim($line);
                if ($trimmed === '' || $trimmed[0] === '#' || $trimmed[0] === ';') {
                    continue;
                }

                // Parser CLÉ=VALEUR
                if (strpos($line, '=') !== false) {
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
