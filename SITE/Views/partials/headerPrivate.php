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
        <nav id="mainnav" class="mainnav" aria-label="Navigation principale" aria-hidden="false">
            <a href="/accueil"<?= ($currentPath === '/accueil' ? ' class="current"' : '') ?>>Accueil</a>
            <a href="/dashboard"<?= ($currentPath === '/dashboard' ? ' class="current"' : '') ?>>Tableau de bord</a>
            <a href="/profile"<?= ($currentPath === '/profile' ? ' class="current"' : '') ?>>Profil</a>
            <!-- Logout en mobile -->
            <a href="/logout" class="nav-login">Deconnexion</a>
        </nav>

        <!-- Dark mode toggle -->
        <button class="dark-mode-toggle" id="darkModeToggle" aria-label="Activer le mode sombre" title="Mode sombre">
            <span class="icon-sun"></span>
            <span class="icon-moon"></span>
        </button>

       <a href="/logout" class="login-btn">Deconnexion</a>


        <!-- Burger menu pour responsive -->
        <button class="burger-menu" aria-label="Menu" aria-expanded="false" aria-controls="mainnav">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </div>
</header>
