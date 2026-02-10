<?php
declare(strict_types=1);

/**
 * Point d'entrée de l'application DashMed
 * @package DashMed
 */

// 1. Configuration sécurisée de la session
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

// 2. En-têtes HTTP de sécurité
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


// =========================================================================
// 3. CHARGEMENT DE L'AUTOLOADER (CORRECTIF ARCHITECTURE PROPRE)
// =========================================================================

$siteDir = dirname(__DIR__) . '/SITE';

// On enregistre une fonction qui va chercher les classes automatiquement
spl_autoload_register(function (string $class) use ($siteDir): void {

    // Définition des dossiers correspondants aux namespaces
    // Namespace => Dossier dans SITE/
    $prefixes = [
        'Core\\'           => '/Core/',
        'Models\\'         => '/Models/',
        'Controllers\\'    => '/Controllers/',
        'Domain\\'         => '/Domain/',         // NOUVEAU
        'Infrastructure\\' => '/Infrastructure/', // NOUVEAU
        'Application\\'    => '/Application/',    // NOUVEAU
        'Views\\'          => '/Views/'
    ];

    foreach ($prefixes as $prefix => $dir) {
        // Vérifie si la classe demandée utilise ce namespace (ex: Domain\Repositories\User...)
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) === 0) {

            // On enlève le préfixe (Domain\) pour garder le reste (Repositories\User...)
            $relativeClass = substr($class, $len);

            // On construit le chemin du fichier : SITE/Domain/Repositories/User... .php
            $file = $siteDir . $dir . str_replace('\\', '/', $relativeClass) . '.php';

            // Si le fichier existe, on le charge
            if (file_exists($file)) {
                require $file;
                return;
            }
        }
    }
});

// =========================================================================
// 4. LANCEMENT DU ROUTEUR
// =========================================================================

use Core\Router;

// Vérification basique pour éviter une erreur fatale si Router n'est pas trouvé
if (!class_exists(Router::class)) {
    die("Erreur Critique : La classe Core\Router est introuvable. Vérifiez que le fichier SITE/Core/Router.php existe.");
}

$router = new Router($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
$router->dispatch();
