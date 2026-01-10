<?php
declare(strict_types=1);

/**
 * Point d'entrée de l'application DashMed.
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
// Ajuster HSTS uniquement si HTTPS est détecté en production
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: no-referrer-when-downgrade');
header('Permissions-Policy: geolocation=(), microphone=()');
if ($isHttps) {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
}
// Basic CSP - tightened with base-uri, form-action, and object-src
header("Content-Security-Policy: default-src 'self'; base-uri 'self'; form-action 'self'; object-src 'none'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com data:; img-src 'self' data:;");
// Remove PHP version header
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

// Dispatch des routes
use Core\Router;

$router = new Router($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
$router->dispatch();
