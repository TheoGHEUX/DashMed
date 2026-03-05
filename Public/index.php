<?php
declare(strict_types=1);

/**
 * Point d'entrée de l'application DashMed
 *
 * Ce fichier démarre l'application et configure la sécurité de base :
 * 1. Configure la session utilisateur de manière sécurisée
 * 2. Active les protections de sécurité du navigateur
 * 3. Charge l'autoloader pour les classes PHP
 * 4. Lance le routeur qui dirige vers la bonne page
 *
 * Sécurité appliquée :
 * - Session protégée contre le vol de cookies
 * - Headers sécurisés pour bloquer les attaques courantes
 * - Force HTTPS en production
 * - Masque les informations sensibles du serveur
 *
 * @package DashMed
 */

// Configuration sécurisée de la session
$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'domain'   => '',
    'secure'   => $secure,
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_name('dashmed_session');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// En-têtes HTTP de sécurité (définis avant toute sortie)
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: no-referrer-when-downgrade');
header('Permissions-Policy: geolocation=(), microphone=()');
if ($isHttps) {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
}
$csp = "default-src 'self'; "
    . "base-uri 'self'; "
    . "form-action 'self'; "
    . "object-src 'none'; "
    . "script-src 'self' 'unsafe-inline'; "
    . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; "
    . "font-src 'self' https://fonts.gstatic.com data:; "
    . "img-src 'self' data:;";
header("Content-Security-Policy: " . $csp);
header('X-Powered-By:');

// Chargement de l'autoloader
$siteDir = __DIR__ . '/../SITE';
$autoLoader = $siteDir . '/Core/AutoLoader.php';

if (is_file($autoLoader)) {
    require $autoLoader;
} else {
    // Autoloader de secours
    spl_autoload_register(function (string $class) use ($siteDir): void {
        $file = $siteDir . '/' . str_replace('\\', '/', $class) . '.php';
        if (is_file($file)) {
            require $file;
        }
    });
}

// Gestion des requêtes spéciales (API Data Generation)
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

if ($uri === '/generate-data' && $method === 'POST') {
    header('Content-Type: application/json');

    // Sécurité : authentification requise
    if (!isset($_SESSION['user'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Accès refusé']);
        exit;
    }

    // Sécurité : validation CSRF via header
    $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if ($csrfToken === '' || !\Core\Csrf::validateWithoutConsuming($csrfToken)) {
        http_response_code(403);
        echo json_encode(['error' => 'Jeton CSRF invalide ou manquant']);
        exit;
    }

    require_once __DIR__ . '/../SITE/Scripts/generate_data_online.php';

    $patientId = $_POST['patient'] ?? 25;
    generatePatientData((int)$patientId, 5);

    echo json_encode(['success' => true]);
    exit;
}

use Core\App;
use Core\Router;

// On initialise le conteneur avant de lancer le routeur
App::init();

// Dispatch des routes
$router = new Router($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
$router->dispatch();