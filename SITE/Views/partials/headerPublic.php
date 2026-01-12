<?php

/**
 * Partial : Header pour visiteurs non connectés
 *
 * Affiche le logo, la navigation publique et le toggle mode sombre.
 *
 * Détecte la page active via REQUEST_URI.
 *
 * Variables attendues :
 *  - $currentPath (string)  Chemin extrait de la requête
 *
 * @package Views
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
