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
        <nav class="mainnav" aria-label="Navigation principale">
            <a href="/accueil"<?= ($currentPath === '/accueil' ? ' class="current"' : '') ?>>Accueil</a>
            <a href="/dashboard"<?= ($currentPath === '/dashboard' ? ' class="current"' : '') ?>>Dashboard</a>
            <a href="/profile"<?= ($currentPath === '/profile' ? ' class="current"' : '') ?>>Profil</a>
        </nav>

       <a href="/logout" class="login-btn">Deconnexion</a>


        <!-- Burger menu pour responsive -->
        <button class="burger-menu" aria-label="Menu" aria-expanded="false">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </div>
</header>
