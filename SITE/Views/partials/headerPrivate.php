<?php

/**
 * Partial : Header pour utilisateurs connectés
 *
 * @package Views
 */

use Core\Domain\Services\NavigationService;
use Core\Domain\Services\AuthenticationService;

$currentPath = NavigationService::getCurrentPath();
$csrfToken = AuthenticationService::getCsrfToken();
?>
<header class="topbar">
    <div class="container">
        <div class="brand">
            <img class="logo" src="/assets/images/logo.png" alt="Logo DashMed">
            <span class="brand-name">DashMed</span>
        </div>

        <nav id="mainnav" class="mainnav" aria-label="Navigation principale" aria-hidden="false">
            <a href="/home"<?= NavigationService::isActive('/home') ?>>Accueil</a>
            <a href="/dashboard"<?= NavigationService::isActive('/dashboard') ?>>Tableau de bord</a>
            <a href="/profile"<?= NavigationService::isActive('/profile') ?>>Profil</a>
            <form action="/logout" method="POST" style="display:inline;margin:0">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <button type="submit" class="nav-login">Déconnexion</button>
            </form>
        </nav>

        <button class="dark-mode-toggle" id="darkModeToggle" aria-label="Activer le mode sombre">
            <span class="icon-sun" aria-hidden="true"></span>
            <span class="icon-moon" aria-hidden="true"></span>
        </button>

        <form action="/logout" method="POST" style="display:inline;margin:0">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
            <button type="submit" class="login-btn">Déconnexion</button>
        </form>

        <button class="burger-menu" aria-label="Menu" aria-expanded="false" aria-controls="mainnav">
            <span aria-hidden="true"></span>
            <span aria-hidden="true"></span>
            <span aria-hidden="true"></span>
        </button>
    </div>
</header>
