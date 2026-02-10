<?php
declare(strict_types=1);

/**
 * Point d'entrée de l'application DashMed
 * Version adaptée pour Architecture MVC Clean
 */

// --- 1. SÉCURITÉ & SESSION (On garde ta configuration excellente) ---
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

// Headers de sécurité
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: no-referrer-when-downgrade');
header('Permissions-Policy: geolocation=(), microphone=()');
if ($isHttps) {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
}

// CSP (Content Security Policy)
$csp = "default-src 'self'; "
    . "base-uri 'self'; "
    . "form-action 'self'; "
    . "object-src 'none'; "
    . "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; " // Ajout cdn pour Chart.js si besoin
    . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; "
    . "font-src 'self' https://fonts.gstatic.com data:; "
    . "img-src 'self' data:;";
header("Content-Security-Policy: " . $csp);
header('X-Powered-By: DashMed-Secure');


// --- 2. CHARGEMENT DU CŒUR (CORE) ---

// Définition du chemin racine vers le dossier SITE
define('ROOT_DIR', dirname(__DIR__) . '/SITE');

// On charge l'Autoloader qu'on a créé précédemment
require_once ROOT_DIR . '/Core/AutoLoader.php';

// On lance l'enregistrement automatique des classes
\Core\AutoLoader::register();


// --- 3. ROUTAGE ---
use Core\Router;

try {
    // On instancie le routeur et on dispatch la requête
    $router = new Router($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
    $router->dispatch();

} catch (Exception $e) {
    // Filet de sécurité global en cas d'erreur critique
    if (ini_get('display_errors')) {
        echo "Erreur Critique : " . $e->getMessage();
    } else {
        // En prod, on log l'erreur et on affiche une page propre
        error_log($e->getMessage());
        http_response_code(500);
        require ROOT_DIR . '/Views/errors/500.php';
    }
}