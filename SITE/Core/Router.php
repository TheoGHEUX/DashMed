<?php

namespace Core;

use Controllers\ConnectedHomeController;
use Controllers\AuthController;
use Controllers\DashboardController;
use Controllers\ForgottenPasswordController;
use Controllers\HomeController;
use Controllers\LegalNoticesController;
use Controllers\MapController;
use Controllers\ProfileController;
use Controllers\ResetPasswordController;
use Controllers\ChangePasswordController;
use Controllers\ChangeEmailController;
use Core\Database;

/**
 * Router principal de l'application
 *
 * Objectif : Orienter les visiteurs vers la bonne page.
 *
 * Analyse l'adresse tapée (URL) et la méthode (GET ou POST)
 * pour appeler le bon contrôleur.
 *
 * Vérifie au passage si l'utilisateur est bien connecté
 * et gère les redirections automatiques.
 *
 * @package Core
 */
final class Router
{
    private string $path;
    private string $method;

    /**
     *
     * Liste les pages accessibles uniquement en envoi de données (POST).
     *
     * Sécurise les actions sensibles (comme la déconnexion) pour empêcher
     * qu'elles ne soient déclenchées par un simple lien ou une image cachée.
     *
     * @var array<string,bool>
     */
    private const POST_ONLY = [
        '/logout' => true,
        '/deconnexion' => true,
    ];

    /**
     * Catalogue central de toutes les adresses du site
     *
     * Organise les pages en trois catégories :
     * - Public : Accessible à tout le monde (visiteurs)
     * - Auth : Pages de passage (connexion, inscription)
     * - Protected : Pages réservées aux praticiens connectés
     *
     * Format : 'adresse' => [ClasseControleur, 'méthodePOST', 'méthodeGET']
     *
     * @var array<string,array<string,array<int,string>>>
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
            '/home' => [ConnectedHomeController::class, 'index'],
            '/dashboard' => [DashboardController::class, 'index'],
            '/tableau-de-bord' => [DashboardController::class, 'index'],
            '/profile' => [ProfileController::class, 'show'],
            '/profil' => [ProfileController::class, 'show'],
            '/change-password' => [ChangePasswordController::class, 'submit', 'showForm'],
            '/changer-mot-de-passe' => [ChangePasswordController::class, 'submit', 'showForm'],
            '/change-email' => [ChangeEmailController::class, 'submit', 'showForm'],
            '/changer-email' => [ChangeEmailController::class, 'submit', 'showForm'],
            '/api/log-graph-action' => [DashboardController::class, 'logGraphAction'],
            '/api/dashboard-layout' => [DashboardController::class, 'getLayout', 'getLayout'],
            '/api/save-dashboard-layout' => [DashboardController::class, 'saveLayout'],
            '/api/suggest-layout' => [DashboardController::class, 'suggestLayout', 'suggestLayout'],
            '/api/check-ai-availability' => [DashboardController::class, 'checkAIAvailability', 'checkAIAvailability'],
            '/api/dashboard/chart-data'  => [DashboardController::class, 'getChartData', 'getChartData'],
        ],
    ];

    /**
     * Construit une instance du routeur.
     *
     * Processus :
     * 1. Reçoit l'adresse tapée (URI) et la nettoie proprement.
     * 2. Extrait uniquement le chemin (ex: retire les paramètres après le "?").
     * 3. S'assure que l'adresse finit sans slash inutile pour éviter les doublons.
     * 4. Enregistre si l'utilisateur veut lire (GET) ou envoyer (POST) des données.
     *
     * @param string $uri     Adresse brute de la page (ex: /profil?id=1)
     * @param string $method  Méthode HTTP (GET, POST...)
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
     * Processus :
     * 1. Traite les tests de santé (health checks) pour vérifier que le site répond.
     * 2. Redirige d'office vers l'accueil privé
     *    si un utilisateur connecté tente d'aller sur l'index.
     * 3. Parcourt la liste des pages publiques (Aide, Mentions légales).
     * 4. Parcourt la liste des pages d'accès (Connexion, Inscription).
     * 5. Parcourt la liste des pages privées (Profil, Dashboard) en vérifiant l'identité.
     * 6. Affiche l'erreur 404 si aucune page ne correspond à l'adresse.
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
            $this->redirect('/home');
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

        $this->handle404();
    }

    /**
     * Tente d'associer l'adresse à une page existante.
     *
     * Supporte deux formats de routes :
     * - Route simple : [Controller::class, 'method'] → méthode unique pour GET et POST
     * - Route double : [Controller::class, 'postMethod', 'getMethod']
     *                  → méthode selon HTTP verb
     *
     * Pour les routes listées dans POST_ONLY, seule la méthode POST est acceptée.
     *
     * Les autres méthodes HTTP reçoivent une erreur 405 Method Not Allowed.
     *
     * @param array $routes       Table des routes
     * @param bool $requiresAuth  True → redirige vers la page de login si non authentifié
     * @return bool               Vrai si la route a été trouvée et exécutée
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
     * - $_SESSION['user']['email_verified'] est défini et truthy
     *
     * @return bool True si l'utilisateur est authentifié avec email vérifié, False sinon
     */
    private function isAuthenticated(): bool
    {
        return !empty($_SESSION['user'])
            && !empty($_SESSION['user']['email_verified']);
    }

    /**
     * Redirection HTTP et termine l'exécution.
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
     * Affiche la page 404 et termine l'exécution.
     *
     * Tente de charger la vue 'errors/404.php'.
     *
     * Si celle-ci n'existe pas, affiche un message HTML simple.
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
     * Renvoie une erreur 405 Method Not Allowed et termine l'exécution.
     *
     * Utilisé lorsqu'une route est définie en POST_ONLY mais que la requête
     * utilise une autre méthode HTTP.
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
