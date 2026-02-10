<?php

namespace Core;

// Importation des classes nécessaires
use Core\Database;
use Core\View;

// Importation de TOUS tes contrôleurs (même ceux qui n'existent pas encore, commente ceux qui manquent)
use Controllers\HomeController;
use Controllers\AuthController;
use Controllers\DashboardController;
// use Controllers\ConnectedHomeController;   // <-- Commente si le fichier n'existe pas encore
// use Controllers\ForgottenPasswordController; // <-- Commente si le fichier n'existe pas encore
// use Controllers\LegalNoticesController;      // <-- Commente si le fichier n'existe pas encore
// use Controllers\MapController;               // <-- Commente si le fichier n'existe pas encore
// use Controllers\ProfileController;           // <-- Commente si le fichier n'existe pas encore
// use Controllers\ResetPasswordController;     // <-- Commente si le fichier n'existe pas encore
// use Controllers\ChangePasswordController;    // <-- Commente si le fichier n'existe pas encore
// use Controllers\ChangeEmailController;       // <-- Commente si le fichier n'existe pas encore
// use Controllers\VerifyEmailController;       // <-- Commente si le fichier n'existe pas encore

/**
 * Router principal - Version Clean MVC (Compatible Windows/XAMPP)
 */
final class Router
{
    private string $path;
    private string $method;

    // Routes accessibles uniquement en POST
    private const POST_ONLY = [
        '/logout' => true,
        '/deconnexion' => true,
    ];

    /**
     * CONFIGURATION DES ROUTES
     * Si une page renvoie 404, vérifie qu'elle est bien listée ici.
     */
    private const ROUTES = [
        'public' => [
            '/' => [HomeController::class, 'index'],
            '/index.php' => [HomeController::class, 'index'],
            // '/map' => [MapController::class, 'show'],
            // '/legal-notices' => [LegalNoticesController::class, 'show'],
        ],
        'auth' => [
            '/login' => [AuthController::class, 'loginPost', 'login'], // POST=loginPost, GET=login
            '/connexion' => [AuthController::class, 'loginPost', 'login'],
            '/logout' => [AuthController::class, 'logout'],
            // '/register' => [AuthController::class, 'register', 'showRegister'],
        ],
        'protected' => [
            '/dashboard' => [DashboardController::class, 'index'],
            '/tableau-de-bord' => [DashboardController::class, 'index'],
            '/api/graph' => [DashboardController::class, 'apiGetChart'],
            // '/profile' => [ProfileController::class, 'show'],
        ],
    ];

    /**
     * Constructeur avec nettoyage automatique du sous-dossier (Correction Windows)
     */
    public function __construct(string $uri, string $method)
    {
        // 1. Récupération du chemin sans les paramètres (?id=1)
        $path = parse_url($uri, PHP_URL_PATH);

        // 2. Détection du dossier d'installation (ex: /Dashmed/Public)
        $scriptDir = dirname($_SERVER['SCRIPT_NAME']);

        // Correction des antislashes Windows (\ -> /)
        $scriptDir = str_replace('\\', '/', $scriptDir);

        // 3. Suppression du dossier racine de l'URL si nécessaire
        // Si l'URL est "/Dashmed/Public/dashboard", on veut juste "/dashboard"
        if ($scriptDir !== '/' && strpos($path, $scriptDir) === 0) {
            $path = substr($path, strlen($scriptDir));
        }

        // 4. Nettoyage final
        $this->path = rtrim($path, '/');

        // Si vide, c'est la racine
        if ($this->path === '') {
            $this->path = '/';
        }

        $this->method = strtoupper($method);
    }

    /**
     * Méthode principale qui lance le contrôleur
     */
    public function dispatch(): void
    {
        // 1. Health check (utile pour les hébergeurs)
        if ($this->path === '/health') {
            header('Content-Type: text/plain'); echo 'OK'; exit;
        }

        // 2. Redirection page d'accueil si déjà connecté
        if (($this->path === '/' || $this->path === '/index.php') && $this->isAuthenticated()) {
            $this->redirect('/dashboard');
        }

        // 3. Tentative de correspondance avec les routes
        if ($this->tryRoute(self::ROUTES['public'])) return;
        if ($this->tryRoute(self::ROUTES['auth'])) return;
        if ($this->tryRoute(self::ROUTES['protected'], true)) return;

        // 4. Si on arrive ici, c'est une 404
        $this->handle404();
    }

    /**
     * Tente de charger une route d'un groupe donné
     */
    private function tryRoute(array $routes, bool $requiresAuth = false): bool
    {
        // Si la route n'existe pas dans ce groupe, on s'arrête tout de suite
        if (!isset($routes[$this->path])) {
            return false;
        }

        // Vérification de sécurité (connexion requise)
        if ($requiresAuth && !$this->isAuthenticated()) {
            $this->redirect('/login');
        }

        // Récupération de la config de la route
        $route = $routes[$this->path];
        $controllerClass = $route[0];
        $postMethod = $route[1];
        $getMethod = $route[2] ?? null;

        // VERIFICATION CRITIQUE : La classe existe-t-elle ?
        if (!class_exists($controllerClass)) {
            // En mode dev, on affiche une erreur explicite pour t'aider
            die("ERREUR FATALE : Le contrôleur <strong>$controllerClass</strong> n'existe pas ou n'est pas importé (use) en haut du fichier Router.php.");
        }

        // Instanciation du contrôleur
        $controller = new $controllerClass();

        // ROUTAGE METHODE (GET vs POST)
        // Cas 1: Méthode unique (ex: logout)
        if ($getMethod === null) {
            if (isset(self::POST_ONLY[$this->path]) && $this->method !== 'POST') {
                $this->methodNotAllowed(['POST']);
            }
            $controller->$postMethod();
            exit;
        }

        // Cas 2: Distinction GET / POST
        if ($this->method === 'POST') {
            if (!method_exists($controller, $postMethod)) {
                die("Erreur : La méthode $postMethod n'existe pas dans $controllerClass");
            }
            $controller->$postMethod();
        } else {
            if (!method_exists($controller, $getMethod)) {
                die("Erreur : La méthode $getMethod n'existe pas dans $controllerClass");
            }
            $controller->$getMethod();
        }
        exit;
    }

    private function isAuthenticated(): bool
    {
        // Adapte selon ta logique de session (ici on vérifie juste user_id)
        return !empty($_SESSION['user_id']);
    }

    private function redirect(string $path): void
    {
        header("Location: $path");
        exit;
    }

    private function handle404(): void
    {
        http_response_code(404);
        // Essaie d'afficher la vue 404, sinon message simple
        try {
            View::render('errors/404');
        } catch (\Exception $e) {
            echo "<h1>404 - Page non trouvée</h1><p>Le chemin '{$this->path}' n'existe pas.</p>";
        }
        exit;
    }

    private function methodNotAllowed(array $allowed): void
    {
        http_response_code(405);
        header('Allow: ' . implode(', ', $allowed));
        echo "Méthode non autorisée. Méthodes permises : " . implode(', ', $allowed);
        exit;
    }
}