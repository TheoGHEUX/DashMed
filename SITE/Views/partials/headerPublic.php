<?php

/**
 * Partial : Header pour visiteurs non connectés
 *
 * @package Views
 */

use Core\Domain\Services\NavigationService;

$currentPath = NavigationService::getCurrentPath();
?>
<header class="topbar">
    <div class="container">
        <div class="brand">
            <img class="logo" src="/assets/images/logo.png" alt="Logo DashMed">
            <span class="brand-name">DashMed</span>
        </div>

        <nav id="mainnav" class="mainnav" aria-label="Navigation principale" aria-hidden="false">
            <a href="/"<?= NavigationService::isActive('/') ?>>Accueil</a>
            <a href="/map"<?= NavigationService::isActive('/map') ?>>Plan du site</a>
            <a href="/mentions-legales"<?= NavigationService::isActiveMultiple(['/mentions-legales', '/legal-notices']) ?>>Mentions légales</a>
            <a href="/login" class="nav-login">Connexion</a>
        </nav>

        <button class="dark-mode-toggle" id="darkModeToggle" aria-label="Activer le mode sombre">
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
