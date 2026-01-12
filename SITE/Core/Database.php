<?php

namespace Core;

use PDO;
use PDOException;

/**
 * Gestionnaire de connexion à la base de données.
 *
 * Lit facultativement un fichier `.env` à la racine pour récupérer les paramètres
 * Utilise des valeurs par défaut si le fichier .env est absent.
 *
 * @package Core
 */
final class Database
{
    private static ?PDO $pdo = null;

    /**
     * Retourne une instance PDO singleton configurée pour MySQL.
     *
     * Configuration :
     * - Lecture du fichier .env (si présent) pour DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS
     * - Valeurs par défaut :  127.0.0.1:3306, dashmed-site_db, root, (pas de mot de passe)
     * - Mode d'erreur : ERRMODE_EXCEPTION
     * - Mode de fetch : FETCH_ASSOC
     * - Emulation de requêtes préparées désactivée
     *
     * @return PDO Connexion PDO
     * @throws PDOException En cas d'échec de connexion
     */
    public static function getConnection(): PDO
    {
        if (self::$pdo === null) {
            // Lecture optionnelle d'un fichier .env à la racine du projet
            $root = dirname(__DIR__, 2);
            $envFile = $root . DIRECTORY_SEPARATOR . '.env';
            $env = [];
            if (is_file($envFile) && is_readable($envFile)) {
                $lines = @file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                if ($lines !== false) {
                    foreach ($lines as $line) {
                        $line = trim($line);
                        if ($line === '' || str_starts_with($line, '#') || str_starts_with($line, ';')) {
                            continue;
                        }
                        if (strpos($line, '=') === false) {
                            continue;
                        }
                        [$k, $v] = explode('=', $line, 2);
                        $k = trim($k);
                        $v = trim($v);
                        $isDoubleQuoted = ($v !== '' && $v[0] === '"' && substr($v, -1) === '"');
                        $isSingleQuoted = ($v !== '' && $v[0] === "'" && substr($v, -1) === "'");
                        if ($isDoubleQuoted || $isSingleQuoted) {
                            $v = substr($v, 1, -1);
                        }
                        $env[$k] = $v;
                    }
                }
            }

            // Priorité aux variables DB_* du .env si présentes
            $host = $env['DB_HOST'] ?? 'mysql-dashmed-site.alwaysdata.net';
            $port = $env['DB_PORT'] ?? '3306';
            $db   = $env['DB_NAME'] ?? 'dashmed-site_db_2';
            $user = $env['DB_USER'] ?? '433165';
            $pass = $env['DB_PASS'] ?? '*Eie(56p/GW82i';

            $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";

            try {
                self::$pdo = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                error_log('DB CONNECTION FAIL: ' . $e->getMessage());
                throw $e;
            }
        }
        return self::$pdo;
    }
}
