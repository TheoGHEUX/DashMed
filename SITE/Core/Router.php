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
        // Nettoyage de l'URI (enlève les slashs de fin et les paramètres GET)
        $path = parse_url($uri, PHP_URL_PATH);
        $this->uri = rtrim($path, '/') ?: '/';
        $this->method = strtoupper($method);

        // Chargement automatique des routes
        $this->registerRoutes();
    }

    /**
     * -----------------------------------------------------------
     * C'EST ICI QUE TU DÉFINIS TES ROUTES
     * -----------------------------------------------------------
     * Utilise $this->get() ou $this->post() pour plus de clarté.
     */
    private function registerRoutes(): void
    {
        // --- AUTHENTIFICATION ---
        $this->get('/login',         'Controllers\AuthController', 'showLogin');
        $this->post('/login',        'Controllers\AuthController', 'login');
        $this->get('/register',      'Controllers\AuthController', 'showRegister');
        $this->post('/register',     'Controllers\AuthController', 'register');
        $this->post('/logout',       'Controllers\AuthController', 'logout');
        $this->get('/verify-email',  'Controllers\AuthController', 'verifyEmail');

        // Mot de passe oublié
        $this->get('/forgotten-password', 'Controllers\ForgottenPasswordController', 'showForm');
        $this->post('/forgotten-password', 'Controllers\ForgottenPasswordController', 'submit');

        // Réinitialisation du mot de passe (Lien cliqué depuis le mail)
        $this->get('/reset-password', 'Controllers\ResetPasswordController', 'showForm');
        $this->post('/reset-password', 'Controllers\ResetPasswordController', 'submit');

        // --- DASHBOARD ---
        $this->get('/dashboard',     'Controllers\DashboardController', 'index');

        // --- PATIENTS ---
        // Exemple : $this->get('/patients', 'Controllers\PatientController', 'index');

        // --- PROFIL & COMPTE ---
        $this->get('/profile/email',  'Controllers\ChangeEmailController', 'showForm');
        $this->post('/profile/email', 'Controllers\ChangeEmailController', 'submit');


        // Page d'accueil (Landing Page)
        $this->get('/', 'Controllers\HomeController', 'index');

        // Mentions Légales
        $this->get('/mentions-legales', 'Controllers\LegalNoticesController', 'show');

        // Plan du site
        $this->get('/map', 'Controllers\MapController', 'show');
    }

    /**
     * Lance le routing
     */
    public function dispatch(): void
    {
        foreach ($this->routes as $route) {
            if ($this->match($route, $params)) {
                $this->run($route['controller'], $route['action'], $params);
                return;
            }
        }

        $this->handleNotFound();
    }

    // --- MÉTHODES UTILITAIRES (Ne pas toucher) ---

    private function get(string $path, string $controller, string $action): void
    {
        $this->add('GET', $path, $controller, $action);
    }

    private function post(string $path, string $controller, string $action): void
    {
        $this->add('POST', $path, $controller, $action);
    }

    private function add(string $method, string $path, string $controller, string $action): void
    {
        // Normalisation : on s'assure que le path commence par / et ne finit pas par /
        $path = '/' . trim($path, '/');
        if ($path === '/') $path = ''; // Cas racine

        $this->routes[] = [
            'method' => $method,
            'pattern' => $path ?: '/',
            'controller' => $controller,
            'action' => $action
        ];
    }

    private function match(array $route, &$params): bool
    {
        if ($this->method !== $route['method']) {
            return false;
        }

        // Si route exacte (optimisation)
        if ($this->uri === $route['pattern']) {
            $params = [];
            return true;
        }

        // Conversion {param} => Regex
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<\1>[a-zA-Z0-9_]+)', $route['pattern']);
        $pattern = "#^" . $pattern . "$#";

        if (preg_match($pattern, $this->uri, $matches)) {
            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            return true;
        }

        return false;
    }

    private function run(string $controllerName, string $actionName, array $params): void
    {
        try {
            if (!class_exists($controllerName)) {
                throw new Exception("Contrôleur introuvable : $controllerName");
            }

            // Injection de dépendances via le Container
            $controller = App::getContainer()->get($controllerName);

            if (!method_exists($controller, $actionName)) {
                throw new Exception("Méthode $actionName introuvable dans $controllerName");
            }

            call_user_func_array([$controller, $actionName], array_values($params));

        } catch (Exception $e) {
            // En prod : loguer l'erreur et afficher une belle 500
            die("<h1>Erreur Routeur</h1><p>" . $e->getMessage() . "</p>");
        }
    }

    private function handleNotFound(): void
    {
        http_response_code(404);
        echo "<h1>404 - Page non trouvée</h1>";
        echo "<p>La route <code>" . htmlspecialchars($this->uri) . "</code> n'existe pas.</p>";
    }
}