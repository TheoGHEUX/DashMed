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
 * Routeur principal de l'application.
 *
 * Résout les requêtes HTTP entrantes vers les contrôleurs appropriés en fonction
 * de l'URI et de la méthode HTTP. Gère l'authentification, les redirections
 * automatiques et les endpoints de diagnostic.
 *
 *
 * @package Core
 */
final class Router
{
    /**
     * Chemin de l'URI demandée (sans query string).
     *
     * @var string
     */
    private string $path;

    /**
     * Méthode HTTP de la requête (GET, POST, etc.).
     *
     * @var string
     */
    private string $method;

    /**
     * Routes nécessitant obligatoirement une requête POST.
     *
     * Utilisé pour sécuriser les actions sensibles (déconnexion).
     *
     * @var array<string,bool>
     */
    private const POST_ONLY = [
        '/logout' => true,
        '/deconnexion' => true,
    ];

    /**
     * Table de routage de l'application.
     *
     * Structure : [groupe => [path => [ControllerClass, postMethod, getMethod]]]
     * - 'public' : Routes accessibles sans authentification
     * - 'auth' : Routes d'authentification (login, register, reset password)
     * - 'protected' : Routes nécessitant une authentification
     *
     * Format d'une route :
     * - 1 méthode : [Controller::class, 'method']
     * - 2 méthodes : [Controller::class, 'postMethod', 'getMethod']
     *
     * @var array<string, array<string, array<int, mixed>>>
     */
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
            '/accueil' => [AccueilController:: class, 'index'],
            '/dashboard' => [DashboardController::class, 'index'],
            '/tableau-de-bord' => [DashboardController::class, 'index'],
            '/profile' => [ProfileController::class, 'show'],
            '/profil' => [ProfileController::class, 'show'],
            '/change-password' => [ChangePasswordController::class, 'submit', 'showForm'],
            '/changer-mot-de-passe' => [ChangePasswordController::class, 'submit', 'showForm'],
            '/change-mail' => [ChangeMailController::class, 'submit', 'showForm'],
            '/changer-mail' => [ChangeMailController:: class, 'submit', 'showForm'],
            '/api/log-graph-action' => [DashboardController::class, 'logGraphAction'],
        ],
    ];

    /**
     * Construit une instance du routeur.
     *
     * Nettoie et normalise l'URI demandée (suppression du trailing slash,
     * extraction du path sans query string).
     *
     * @param string $uri URI complète de la requête (ex: /dashboard? id=1)
     * @param string $method Méthode HTTP (GET, POST, PUT, DELETE, etc.)
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
     * Processus de résolution :
     * 1. Vérifie les endpoints de diagnostic (/health, /health/db)
     * 2. Redirige automatiquement les utilisateurs connectés de / vers /accueil
     * 3. Tente les routes publiques
     * 4. Tente les routes d'authentification
     * 5. Tente les routes protégées (avec vérification d'authentification)
     * 6. Affiche une page 404 si aucune route ne correspond
     *
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

        // Diagnostic DB restreint (réduit l'exposition en debug)
        if ($this->path === '/health/db') {
            // Lecture minimale de .env pour APP_DEBUG/HEALTH_KEY
            $root = dirname(__DIR__, 2);
            $envFile = $root .  DIRECTORY_SEPARATOR . '.env';
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

            header('Content-Type:  application/json; charset=utf-8');
            try {
                $pdo = Database::getConnection();
                $dbName = $pdo->query('SELECT DATABASE()')->fetchColumn() ?: null;
                echo json_encode([
                    'status'   => 'ok',
                    'database' => $dbName,
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            } catch (\Throwable $e) {
                http_response_code(500);
                echo json_encode(['error' => 'db_unavailable']);
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
     * Tente de résoudre et exécuter une route depuis une table de routage.
     *
     * Supporte deux formats de routes :
     * - Route simple : [Controller::class, 'method'] → méthode unique
     * - Route double : [Controller::class, 'postMethod', 'getMethod'] → méthode selon HTTP verb
     *
     * Pour les routes listées dans POST_ONLY, seule la méthode POST est acceptée.
     * Les autres méthodes HTTP reçoivent une erreur 405 Method Not Allowed.
     *
     * @param array<string, array<int, mixed>> $routes Table de routage à vérifier
     * @param bool $requiresAuth Si true, redirige vers /login si l'utilisateur n'est pas authentifié
     * @return bool True si la route a été trouvée et exécutée, false sinon
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
     * Vérifie si un utilisateur est authentifié.
     *
     * Un utilisateur est considéré authentifié si :
     * - $_SESSION['user'] existe et n'est pas vide
     * - $_SESSION['user']['email_verified'] est défini et non vide
     *
     * @return bool True si l'utilisateur est authentifié avec email vérifié, false sinon
     */
    private function isAuthenticated(): bool
    {
        return !empty($_SESSION['user'])
            && !empty($_SESSION['user']['email_verified']);
    }

    /**
     * Effectue une redirection HTTP et termine l'exécution.
     *
     * @param string $path Chemin de destination (ex: /login, /dashboard)
     * @return void
     */
    private function redirect(string $path): void
    {
        header("Location:  $path");
        exit;
    }

    /**
     * Affiche la page d'erreur 404 et termine l'exécution.
     *
     * Tente de charger la vue 'errors/404.php'.  Si celle-ci n'existe pas,
     * affiche un message texte simple.
     *
     * @return void
     */
    private function handle404(): void
    {
        http_response_code(404);
        if (file_exists(__DIR__ . '/../Views/errors/404.php')) {
            \Core\View::render('errors/404');
        } else {
            echo '404 - Page non trouvée';
        }
        exit;
    }

    /**
     * Retourne une erreur 405 Method Not Allowed et termine l'exécution.
     *
     * Utilisé lorsqu'une route est définie en POST_ONLY mais que la requête
     * utilise une autre méthode HTTP (ex: GET sur /logout).
     *
     * @param array<int,string> $allowed Liste des méthodes HTTP acceptées (ex: ['POST'])
     * @return void
     */
    private function methodNotAllowed(array $allowed): void
    {
        http_response_code(405);
        header('Allow: ' . implode(', ', $allowed));
        exit;
    }
}