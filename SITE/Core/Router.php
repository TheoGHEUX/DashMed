<?php

declare(strict_types=1);

namespace Core;

// --- 1. Importation des contrôleurs avec les BONS dossiers ---

// Dossier Public
use App\Controllers\Public\HomeController;
use App\Controllers\Public\MapController;
use App\Controllers\Public\LegalNoticesController;

// Dossier Authentication (Tu n'as plus AuthController, mais des fichiers séparés)
use App\Controllers\Authentication\LoginController;
use App\Controllers\Authentication\RegisterController;
use App\Controllers\Authentication\LogoutController;
use App\Controllers\Authentication\ForgottenPasswordController;
use App\Controllers\Authentication\ResetPasswordController;
use App\Controllers\Authentication\VerifyEmailController;

// Dossier Private
use App\Controllers\Private\ConnectedHomeController;

// Dossier Dashboard
use App\Controllers\Dashboard\DashboardController;
use App\Controllers\Dashboard\ChartApiController;
use App\Controllers\Dashboard\LayoutApiController;
use App\Controllers\Dashboard\IntelligenceApiController;

// Dossier Profile
use App\Controllers\Profile\ProfileController;
use App\Controllers\Profile\ChangePasswordController;
use App\Controllers\Profile\ChangeEmailController;


final class Router
{
    private const ROUTES = [
        'GET' => [
            // --- Pages Publiques ---
            '/' => [HomeController::class, 'index'],
            '/index.php' => [HomeController::class, 'index'],
            '/map' => [MapController::class, 'show'],
            '/legal-notices' => [LegalNoticesController::class, 'show'],
            '/mentions-legales' => [LegalNoticesController::class, 'show'],

            // --- Authentification (Affichage des formulaires) ---
            // Attention : J'assume que tes méthodes s'appellent 'show'.
            // Si c'est 'index' ou 'showLogin', change le 2ème paramètre.
            '/login' => [LoginController::class, 'show'],
            '/connexion' => [LoginController::class, 'show'],
            '/register' => [RegisterController::class, 'show'],
            '/inscription' => [RegisterController::class, 'show'],

            '/forgotten-password' => [ForgottenPasswordController::class, 'showForm'],
            '/reset-password' => [ResetPasswordController::class, 'showForm'],
            '/verify-email' => [VerifyEmailController::class, 'verify'],

            // --- Espace Privé ---
            '/home' => [ConnectedHomeController::class, 'index'],
            '/dashboard' => [DashboardController::class, 'index'],
            '/profile' => [ProfileController::class, 'show'],
            '/profil' => [ProfileController::class, 'show'],
            '/change-password' => [ChangePasswordController::class, 'showForm'],
            '/change-email' => [ChangeEmailController::class, 'showForm'],

            // --- API ---
            '/api/dashboard-layout' => [LayoutApiController::class, 'load'],
            '/api/dashboard/chart-data' => [ChartApiController::class, 'getData'],
            '/api/suggest-layout' => [LayoutApiController::class, 'suggest'],
            '/health' => [self::class, 'healthCheck'],
        ],

        'POST' => [
            // --- Authentification (Actions) ---
            '/login' => [LoginController::class, 'login'],       // Méthode de soumission
            '/register' => [RegisterController::class, 'register'], // Méthode de soumission
            '/logout' => [LogoutController::class, 'logout'],
            '/deconnexion' => [LogoutController::class, 'logout'],

            '/forgotten-password' => [ForgottenPasswordController::class, 'submit'],
            '/reset-password' => [ResetPasswordController::class, 'submit'],
            '/resend-verification' => [VerifyEmailController::class, 'resend'],

            // --- Profil ---
            '/change-password' => [ChangePasswordController::class, 'submit'],
            '/change-email' => [ChangeEmailController::class, 'submit'],

            // --- API ---
            '/api/save-dashboard-layout' => [LayoutApiController::class, 'save'],
            '/api/log-graph-action' => [IntelligenceApiController::class, 'logAction'],
            '/api/predict-action' => [IntelligenceApiController::class, 'predict'],
            '/generate-data' => [ChartApiController::class, 'generateData'],
        ],
    ];

    public function dispatch(): void
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH);

        if ($path !== '/' && substr($path, -1) === '/') {
            $path = rtrim($path, '/');
        }

        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

        if (isset(self::ROUTES[$method][$path])) {
            [$controllerClass, $action] = self::ROUTES[$method][$path];

            if ($controllerClass === self::class) {
                $this->$action();
                return;
            }

            if (class_exists($controllerClass)) {
                $controller = new $controllerClass();
                if (method_exists($controller, $action)) {
                    $controller->$action();
                    return;
                } else {
                    // Erreur si la méthode n'existe pas dans le contrôleur
                    die("Erreur 500 : La méthode '$action' est introuvable dans '$controllerClass'.");
                }
            } else {
                // Erreur si la classe est introuvable
                die("Erreur 500 : La classe '$controllerClass' est introuvable. Vérifiez les namespaces.");
            }
        }

        $this->handle404();
    }

    private function handle404(): void
    {
        http_response_code(404);
        echo "<h1>404 - Page non trouvée</h1>";
        exit;
    }

    private function healthCheck(): void
    {
        echo 'OK';
        exit;
    }
}