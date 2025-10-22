<?php
declare(strict_types=1);

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
