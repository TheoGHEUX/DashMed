<?php

namespace Core;

use Core\App;
use Exception;

class Router
{
    private string $uri;
    private string $method;
    private array $routes = [];

    public function __construct(string $uri, string $method)
    {
        $path = parse_url($uri, PHP_URL_PATH);
        $this->uri = rtrim($path, '/') ?: '/';
        $this->method = strtoupper($method);
        $this->registerRoutes();
    }

    private function registerRoutes(): void
    {
        // ====================================================================
        // 1. PAGES PUBLIQUES
        // ====================================================================
        $this->get('/', 'Controllers\HomeController', 'index');
        $this->get('/mentions-legales', 'Controllers\LegalNoticesController', 'show');
        $this->get('/map', 'Controllers\MapController', 'show');

        // ====================================================================
        // 2. AUTHENTIFICATION (Login, Register, Logout)
        // ====================================================================

        // Connexion
        $this->get('/login',  'Controllers\AuthController', 'showLogin');
        $this->post('/login', 'Controllers\AuthController', 'login');
        $this->post('/logout','Controllers\AuthController', 'logout');

        // Inscription
        $this->get('/register',  'Controllers\AuthController', 'showRegister');
        $this->post('/register', 'Controllers\AuthController', 'register');

        // ====================================================================
        // 3. GESTION DES EMAILS & MOTS DE PASSE (Séparé du AuthController)
        // ====================================================================

        // Vérification Email (Le lien cliqué dans le mail)
        $this->get('/verify-email', 'Controllers\VerifyEmailController', 'verify');

        // Renvoi de l'email de vérification (Formulaire + Action)
        $this->get('/resend-verification',  'Controllers\VerifyEmailController', 'showResendForm');
        $this->post('/resend-verification', 'Controllers\VerifyEmailController', 'resend');

        // Mot de passe oublié (Demande)
        $this->get('/forgotten-password',  'Controllers\ForgottenPasswordController', 'showForm');
        $this->post('/forgotten-password', 'Controllers\ForgottenPasswordController', 'submit');

        // Réinitialisation du mot de passe (Après clic email)
        $this->get('/reset-password',  'Controllers\ResetPasswordController', 'showForm');
        $this->post('/reset-password', 'Controllers\ResetPasswordController', 'submit');

        // ====================================================================
        // 4. ESPACE CONNECTÉ
        // ====================================================================

        $this->get('/home', 'Controllers\ConnectedHomeController', 'index');
        $this->get('/dashboard', 'Controllers\DashboardController', 'index');

        // --- PROFIL ---
        $this->get('/profile', 'Controllers\ProfileController', 'show');

        // Changement d'email
        $this->get('/profile/email',  'Controllers\ChangeEmailController', 'showForm');
        $this->post('/profile/email', 'Controllers\ChangeEmailController', 'submit');

        // Changement de mot de passe
        $this->get('/profile/password',  'Controllers\ChangePasswordController', 'showForm');
        $this->post('/profile/password', 'Controllers\ChangePasswordController', 'submit');
    }

    public function dispatch(): void
    {
        $params = [];
        foreach ($this->routes as $route) {
            if ($this->match($route, $params)) {
                $this->run($route['controller'], $route['action'], $params);
                return;
            }
        }
        $this->handleNotFound();
    }

    // --- Helpers (get, post, add, match, run, handleNotFound) ---

    private function get(string $path, string $controller, string $action): void { $this->add('GET', $path, $controller, $action); }
    private function post(string $path, string $controller, string $action): void { $this->add('POST', $path, $controller, $action); }

    private function add(string $method, string $path, string $controller, string $action): void {
        $path = '/' . trim($path, '/');
        if ($path === '/') $path = '';
        $this->routes[] = ['method' => $method, 'pattern' => $path ?: '/', 'controller' => $controller, 'action' => $action];
    }

    private function match(array $route, &$params): bool {
        if ($this->method !== $route['method']) return false;
        if ($this->uri === $route['pattern']) { $params = []; return true; }
        $pattern = "#^" . preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<\1>[a-zA-Z0-9_]+)', $route['pattern']) . "$#";
        if (preg_match($pattern, $this->uri, $matches)) {
            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            return true;
        }
        return false;
    }

    private function run(string $controllerName, string $actionName, array $params): void {
        try {
            if (!class_exists($controllerName)) throw new Exception("Contrôleur introuvable : $controllerName");
            $controller = App::getContainer()->get($controllerName);
            if (!method_exists($controller, $actionName)) throw new Exception("Méthode $actionName introuvable");
            call_user_func_array([$controller, $actionName], array_values($params));
        } catch (Exception $e) {
            die("<h1>Erreur Routeur</h1><p>" . $e->getMessage() . "</p>");
        }
    }

    private function handleNotFound(): void {
        http_response_code(404);
        echo "<h1>404 - Page non trouvée</h1>";
    }
}