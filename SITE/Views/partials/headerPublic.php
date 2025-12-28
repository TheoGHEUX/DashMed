<?php

/**
 * Partial : Header public (navigation pour visiteurs non authentifiés)
 *
 * En-tête simple et réutilisable affichant le logo, la navigation publique,
 * le toggle mode sombre et le menu burger. Détecte la page active via REQUEST_URI.
 *
 * Variables attendues :
 * @var string $currentPath Chemin extrait de la requête
 *
 * @package    DashMed
 * @subpackage Views\Partials
 * @category   Frontend
 * @version    1.0
 * @since      1.0
 */
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
?>
<header class="topbar">
    <div class="container">
        <div class="brand">
            <img class="logo" src="/assets/images/logo.png" alt="Logo DashMed">
            <span class="brand-name">DashMed</span>
        </div>

        <nav id="mainnav" class="mainnav" aria-label="Navigation principale" aria-hidden="false">
            <a href="/"<?= ($currentPath === '/' ? ' class="current"' : '') ?>>Accueil</a>
            <a href="/map"<?= ($currentPath === '/map' ? ' class="current"' : '') ?>>Plan du site</a>
            <a href="/mentions-legales"
                <?= ($currentPath === '/mentions-legales' || $currentPath === '/legal-notices'
                    ? ' class="current"' : '') ?>>Mentions légales</a>
            <a href="/login" class="nav-login">Connexion</a>
        </nav>

        <button class="dark-mode-toggle" id="darkModeToggle" aria-label="Activer le mode sombre" title="Mode sombre">
            <span class="icon-sun" aria-hidden="true"></span>
            <span class="icon-moon" aria-hidden="true"></span>
        </button>

        <a href="/login" class="login-btn">Connexion</a>

        <button class="burger-menu" aria-label="Menu" aria-expanded="false" aria-controls="mainnav">
            <span aria-hidden="true"></span>
            <span aria-hidden="true"></span>
            <span aria-hidden="true"></span>
        </button>
    </div>
</header>