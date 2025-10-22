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
