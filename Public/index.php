<?php


declare(strict_types=1);
putenv('APP_DEBUG=1'); // Force l'affichage des erreurs
/**
 * Point d'entrée unique de l'application DashMed.
 * Version : Clean Architecture
 */

// 1. Configuration de la Session (Sécurité)
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

// 2. En-têtes HTTP de sécurité (CSP, HSTS...)
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: no-referrer-when-downgrade');
header('Permissions-Policy: geolocation=(), microphone=()');

if ($isHttps) {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
}

// Construction de la Content-Security-Policy (CSP)
$csp = "default-src 'self'; "
    . "base-uri 'self'; "
    . "form-action 'self'; "
    . "object-src 'none'; "
    . "script-src 'self' 'unsafe-inline'; " // 'unsafe-inline' nécessaire pour certains scripts JS inline
    . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; "
    . "font-src 'self' https://fonts.gstatic.com data:; "
    . "img-src 'self' data:; "
    . "connect-src 'self';";

if ($isHttps) {
    $csp .= " upgrade-insecure-requests; frame-ancestors 'none';";
}

header("Content-Security-Policy: " . $csp);
header('X-Powered-By: DashMed'); // Ou vide pour masquer PHP

// 3. Chargement de l'Autoloader
// Ajuste le chemin "../SITE" si ton index.php n'est pas dans un dossier "public"
$autoLoaderPath = __DIR__ . '/../SITE/Core/AutoLoader.php';

if (file_exists($autoLoaderPath)) {
    require_once $autoLoaderPath;
} else {
    // Fallback si l'autoloader Core est introuvable (Debug)
    die("Erreur critique : Impossible de charger l'application (AutoLoader introuvable).");
}

use Core\Router;

// 4. Lancement du Routeur
try {
    // Le routeur charge automatiquement la config depuis App/Config/Routes.php
    $router = new Router();

    // Il analyse $_SERVER['REQUEST_URI'] et lance le bon contrôleur
    $router->dispatch();

} catch (Throwable $e) {
    // Filet de sécurité global pour les erreurs non gérées
    http_response_code(500);

    // En mode debug (local), on affiche l'erreur
    if (getenv('APP_DEBUG') === '1') {
        echo "<h1>Erreur Critique</h1>";
        echo "<pre>" . $e->getMessage() . "\n" . $e->getTraceAsString() . "</pre>";
    } else {
        // En production, message générique
        echo "<h1>Une erreur interne est survenue.</h1>";
        error_log($e->getMessage()); // Log serveur
    }
}