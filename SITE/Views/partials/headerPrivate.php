<?php

/**
 * Partial : Header privé (navigation pour utilisateurs authentifiés)
 *
 * En-tête affiché pour les utilisateurs connectés : logo, navigation privée
 * (Accueil, Tableau de bord, Profil), toggle mode sombre, et bouton Déconnexion.
 *
 * @package    DashMed
 * @subpackage Views\Partials
 * @category   Frontend
 * @version    1.0
 *
 * Variables attendues :
 * @var string $currentPath Chemin extrait de la requête
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
            <a href="/accueil"<?= ($currentPath === '/accueil' ? ' class="current"' : '') ?>>Accueil</a>
            <a href="/dashboard"<?= ($currentPath === '/dashboard' ? ' class="current"' : '') ?>>Tableau de bord</a>
            <a href="/profile"<?= ($currentPath === '/profile' ? ' class="current"' : '') ?>>Profil</a>
            <a href="/logout" class="nav-login">Déconnexion</a>
        </nav>

        <button class="dark-mode-toggle" id="darkModeToggle" aria-label="Activer le mode sombre" title="Mode sombre">
            <span class="icon-sun" aria-hidden="true"></span>
            <span class="icon-moon" aria-hidden="true"></span>
        </button>

        <a href="/logout" class="login-btn">Déconnexion</a>

        <button class="burger-menu" aria-label="Menu" aria-expanded="false" aria-controls="mainnav">
            <span aria-hidden="true"></span>
            <span aria-hidden="true"></span>
            <span aria-hidden="true"></span>
        </button>
    </div>
</header>