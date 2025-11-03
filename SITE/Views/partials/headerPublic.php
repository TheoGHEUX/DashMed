<?php
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
?>
<header class="topbar">
    <div class="container">
        <!-- Logo et nom du projet -->
        <div class="brand">
            <img class="logo" src="/assets/images/logo.png" alt="Logo DashMed">
            <span class="brand-name">DashMed</span>
        </div>

        <!-- Navigation principale -->
        <!-- Navigation principale -->
        <nav id="mainnav" class="mainnav" aria-label="Navigation principale" aria-hidden="false">
            <a href="/"<?= ($currentPath === '/' ? ' class="current"' : '') ?>>Accueil</a>
            <a href="/map"<?= ($currentPath === '/map' ? ' class="current"' : '') ?>>Plan du site</a>
            <a href="/mentions-legales"<?= ($currentPath === '/mentions-legales' || $currentPath === '/legal-notices' ? ' class="current"' : '') ?>>Mentions légales</a>
            <!-- Login affich\u00e9 en mobile dans le menu -->
            <a href="/login" class="nav-login">Connexion</a>
        </nav>
        <!-- Bouton de connexion visible sur desktop (masqu\u00e9 sur mobile via CSS) -->
        <a href="/login" class="login-btn">Connexion</a>


        <!-- Burger menu pour responsive -->
        <button class="burger-menu" aria-label="Menu" aria-expanded="false" aria-controls="mainnav">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </div>
</header>
