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


final class Router
{
    private string $path;
    private string $method;
    
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

    public function __construct(string $uri, string $method)
    {
        $this->path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $this->path = rtrim($this->path, '/');
        if ($this->path === '') {
            $this->path = '/';
        }
        $this->method = strtoupper($method);
    }

    public function dispatch(): void
    {
        // Health check
        if ($this->path === '/health') {
            header('Content-Type: text/plain; charset=utf-8');
            echo 'OK';
            exit;
        }

        // DB health (diagnostic sécurisé)
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
                        if ($line === '' || str_starts_with($line, '#') || str_starts_with($line, ';')) continue;
                        if (strpos($line, '=') === false) continue;
                        [$k, $v] = explode('=', $line, 2);
                        $k = trim($k); $v = trim($v);
                        if ($v !== '' && (($v[0] === '"' && substr($v, -1) === '"') || ($v[0] === "'" && substr($v, -1) === "'"))) {
                            $v = substr($v, 1, -1);
                        }
                        $env[$k] = $v;
                    }
                }
            }

            $debug = ($env['APP_DEBUG'] ?? '') === '1';
            $keyOk = isset($_GET['key']) && ($_GET['key'] === ($env['HEALTH_KEY'] ?? ''));
            if (!$debug && !$keyOk) {
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
                if ($q) { $tables = $q->fetchAll(\PDO::FETCH_COLUMN) ?: []; }

                $columns = [];
                foreach (['medecin', 'users'] as $t) {
                    try {
                        $st = $pdo->query('SHOW COLUMNS FROM `'.$t.'`');
                        if ($st) {
                            $columns[$t] = $st->fetchAll(\PDO::FETCH_COLUMN) ?: [];
                        }
                    } catch (\Throwable $e) { /* ignore */ }
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

        // Redirection automatique si connecté sur la page d'accueil publique
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

    private function tryRoute(array $routes, bool $requiresAuth = false): bool
    {
        if (!isset($routes[$this->path])) {
            return false;
        }

        // Vérification de l'authentification si nécessaire
        if ($requiresAuth && !$this->isAuthenticated()) {
            $this->redirect('/login');
        }

        $route = $routes[$this->path];
        [$controllerClass, $postMethod, $getMethod] = array_pad($route, 3, null);

        $controller = new $controllerClass();
        
        // Si c'est une route avec méthode unique (ex: logout, show)
        if ($getMethod === null) {
            $controller->$postMethod();
            exit;
        }

        // Route avec GET et POST différents
        if ($this->method === 'POST') {
            $controller->$postMethod();
        } else {
            $controller->$getMethod();
        }
        exit;
    }

    private function isAuthenticated(): bool
    {
        return !empty($_SESSION['user']);
    }

    private function redirect(string $path): void
    {
        header("Location: $path");
        exit;
    }

    private function handle404(): void
    {
        http_response_code(404);
        // TODO: Créer une vue 404 appropriée
        if (file_exists(__DIR__ . '/../Views/errors/404.php')) {
            \View::render('errors/404');
        } else {
            echo '404 - Page non trouvée';
        }
        exit;
    }
}
