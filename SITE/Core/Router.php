<?php

namespace Core;

use Controllers\AccueilController;
use Controllers\AuthController;
use Controllers\DashboardController;
use Controllers\ForgottenPasswordController;
use Controllers\HomeController;
use Controllers\LegalNoticesController;
use Controllers\MapController;
use Controllers\ProfileController;
use Controllers\ResetPasswordController;
use Controllers\ChangePasswordController;
use Controllers\ChangeMailController;
use Core\Database;

/**
 * Router minimal de l'application.
 *
 * Résout les routes définies dans la constante ROUTES et appelle les méthodes
 * des contrôleurs correspondants. Gère également quelques endpoints de
 * diagnostic (`/health`, `/health/db`).
 */
final class Router
{
    private string $path;
    private string $method;

    /** @var array<string,bool> */
    private const POST_ONLY = [
        '/logout' => true,
        '/deconnexion' => true,
    ];

    private const ROUTES = [
        'public' => [
            '/' => [HomeController::class, 'index'],
            '/index.php' => [HomeController::class, 'index'],
            '/map' => [MapController::class, 'show'],
            '/legal-notices' => [LegalNoticesController::class, 'show'],
            '/mentions-legales' => [LegalNoticesController::class, 'show'],
        ],
        'auth' => [
            '/register' => [AuthController::class, 'register', 'showRegister'],
            '/inscription' => [AuthController::class, 'register', 'showRegister'],
            '/login' => [AuthController::class, 'login', 'showLogin'],
            '/connexion' => [AuthController::class, 'login', 'showLogin'],
            '/logout' => [AuthController::class, 'logout'],
            '/deconnexion' => [AuthController::class, 'logout'],
            '/forgotten-password' => [ForgottenPasswordController::class, 'submit', 'showForm'],
            '/mot-de-passe-oublie' => [ForgottenPasswordController::class, 'submit', 'showForm'],
            '/reset-password' => [ResetPasswordController::class, 'submit', 'showForm'],
            '/verify-email' => [\Controllers\VerifyEmailController::class, 'verify'],
            '/resend-verification' => [\Controllers\VerifyEmailController::class, 'resend', 'resend'],
        ],
        'protected' => [
            '/accueil' => [AccueilController::class, 'index'],
            '/dashboard' => [DashboardController::class, 'index'],
            '/tableau-de-bord' => [DashboardController::class, 'index'],
            '/profile' => [ProfileController::class, 'show'],
            '/profil' => [ProfileController::class, 'show'],
            '/change-password' => [ChangePasswordController::class, 'submit', 'showForm'],
            '/changer-mot-de-passe' => [ChangePasswordController::class, 'submit', 'showForm'],
            '/change-mail' => [ChangeMailController::class, 'submit', 'showForm'],
            '/changer-mail' => [ChangeMailController::class, 'submit', 'showForm'],
        ],
    ];

    /**
     * @param string $uri URI demandée
     * @param string $method Méthode HTTP (GET, POST...)
     */
    public function __construct(string $uri, string $method)
    {
        $this->path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $this->path = rtrim($this->path, '/');
        if ($this->path === '') {
            $this->path = '/';
        }
        $this->method = strtoupper($method);
    }

    /**
     * Résout la route et exécute le contrôleur associé.
     *
     * Peut aussi répondre aux endpoints de diagnostic `/health` et `/health/db`.
     *
     * @return void
     */
    public function dispatch(): void
    {
        // Endpoint simple pour vérifier que l'application répond
        if ($this->path === '/health') {
            header('Content-Type: text/plain; charset=utf-8');
            echo 'OK';
            exit;
        }

        // Diagnostic DB restreint
        if ($this->path === '/health/db') {
            // Lecture minimale de .env pour APP_DEBUG/HEALTH_KEY
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

            $debug = ($env['APP_DEBUG'] ?? '') === '1';
            $keyOk = isset($_GET['key']) && ($_GET['key'] === ($env['HEALTH_KEY'] ?? ''));
            // Ne pas exposer en production : seulement si APP_DEBUG=1 ET clé correcte
            if (!$debug) {
                http_response_code(404);
                exit;
            }
            if (!$keyOk) {
                http_response_code(403);
                header('Content-Type: text/plain; charset=utf-8');
                echo 'Forbidden';
                exit;
            }

            header('Content-Type: application/json; charset=utf-8');
            try {
                $pdo = Database::getConnection();
                $dbName = $pdo->query('SELECT DATABASE()')->fetchColumn() ?: null;
                $tables = [];
                $q = $pdo->query('SHOW TABLES');
                if ($q) {
                    $tables = $q->fetchAll(\PDO::FETCH_COLUMN) ?: [];
                }

                $columns = [];
                foreach (['medecin', 'users'] as $t) {
                    try {
                        $st = $pdo->query('SHOW COLUMNS FROM `' . $t . '`');
                        if ($st) {
                            $columns[$t] = $st->fetchAll(\PDO::FETCH_COLUMN) ?: [];
                        }
                    } catch (\Throwable $e) {
/* ignore */
                    }
                }

                echo json_encode([
                    'database' => $dbName,
                    'tables'   => $tables,
                    'columns'  => $columns,
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            } catch (\Throwable $e) {
                http_response_code(500);
                echo json_encode(['error' => $e->getMessage()]);
            }
            exit;
        }

        // Redirection automatique si l'utilisateur est connecté et visite la page publique d'accueil
        if (($this->path === '/' || $this->path === '/index.php') && $this->isAuthenticated()) {
            $this->redirect('/accueil');
        }

        // Tentative de résolution de la route
        if ($this->tryRoute(self::ROUTES['public'])) {
            return;
        }

        if ($this->tryRoute(self::ROUTES['auth'])) {
            return;
        }

        if ($this->tryRoute(self::ROUTES['protected'], true)) {
            return;
        }

        // 404 - Page non trouvée
        $this->handle404();
    }

    /**
     * Tente d'exécuter une route définie dans $routes.
     *
     * @param array $routes Table des routes
     * @param bool $requiresAuth Si true, redirige vers la page de login si non authentifié
     * @return bool Vrai si la route a été trouvée et exécutée
     */
    private function tryRoute(array $routes, bool $requiresAuth = false): bool
    {
        if (!isset($routes[$this->path])) {
            return false;
        }

        if ($requiresAuth && !$this->isAuthenticated()) {
            $this->redirect('/login');
        }

        $route = $routes[$this->path];
        [$controllerClass, $postMethod, $getMethod] = array_pad($route, 3, null);

        $controller = new $controllerClass();

        // Route avec une seule méthode (ex: logout, show)
        if ($getMethod === null) {
            if (isset(self::POST_ONLY[$this->path]) && $this->method !== 'POST') {
                $this->methodNotAllowed(['POST']);
            }
            $controller->$postMethod();
            exit;
        }

        // Route distinguant POST et GET
        if ($this->method === 'POST') {
            $controller->$postMethod();
        } else {
            $controller->$getMethod();
        }
        exit;
    }

    /**
     * Indique si un utilisateur est authentifié (présence en session).
     *
     * @return bool
     */
    private function isAuthenticated(): bool
    {
        return !empty($_SESSION['user'])
            && !empty($_SESSION['user']['email_verified']);
    }

    /**
     * Redirection HTTP et sortie immédiate.
     *
     * @param string $path Chemin vers lequel rediriger
     * @return void
     */
    private function redirect(string $path): void
    {
        header("Location: $path");
        exit;
    }

    /**
     * Affiche la page 404 (si disponible) puis termine l'exécution.
     *
     * @return void
     */
    private function handle404(): void
    {
        http_response_code(404);
        if (file_exists(__DIR__ . '/../Views/errors/404.php')) {
            \View::render('errors/404');
        } else {
            echo '404 - Page non trouvée';
        }
        exit;
    }

    /**
     * Répond 405 Method Not Allowed et arrête l'exécution.
     *
     * @param array<int,string> $allowed Liste des méthodes acceptées
     * @return void
     */
    private function methodNotAllowed(array $allowed): void
    {
        http_response_code(405);
        header('Allow: ' . implode(', ', $allowed));
        exit;
    }
}
