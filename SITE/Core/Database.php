<?php

namespace Core;

use PDO;
use PDOException;

/**
 * Gestionnaire de connexion à la base de données.
 *
 * Fournit une connexion PDO singleton configurée pour MySQL.
 * Lit les paramètres de connexion depuis un fichier . env à la racine du projet,
 * ou utilise des valeurs par défaut si celui-ci est absent.
 *
 * @package Core
 */
final class Database
{
    /**
     * Instance PDO partagée (singleton).
     *
     * @var PDO|null
     */
    private static ?PDO $pdo = null;

    /**
     * Retourne une instance PDO singleton configurée pour MySQL.
     *
     * La connexion est créée lors du premier appel et réutilisée ensuite.
     * Les paramètres de connexion sont lus depuis le fichier .env :
     * - DB_HOST :  Hôte de la base de données (défaut : 127.0.0.1)
     * - DB_PORT : Port MySQL (défaut : 3306)
     * - DB_NAME :  Nom de la base de données (défaut : dashmed-site_db)
     * - DB_USER : Utilisateur de connexion (défaut : root)
     * - DB_PASS :  Mot de passe (défaut : chaîne vide)
     *
     * Configuration PDO appliquée :
     * - ERRMODE_EXCEPTION : Lance des exceptions en cas d'erreur
     * - FETCH_ASSOC : Retourne les résultats sous forme de tableaux associatifs
     * - EMULATE_PREPARES=false : Utilise les vraies requêtes préparées natives
     *
     * @return PDO Connexion PDO configurée et prête à l'emploi
     * @throws PDOException En cas d'échec de connexion à la base de données
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
            $host = $env['DB_HOST'] ??  '127.0.0.1';
            $port = $env['DB_PORT'] ?? '3306';
            $db   = $env['DB_NAME'] ??  'dashmed-site_db';
            $user = $env['DB_USER'] ?? 'root';
            $pass = $env['DB_PASS'] ??  '';

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