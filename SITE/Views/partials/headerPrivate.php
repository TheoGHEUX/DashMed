<?php

/**
 * Partial : Header privé
 *
 * Navigation pour utilisateurs authentifiés (Accueil, Tableau de bord, Profil,
 * Déconnexion). Inclut le toggle mode sombre, le menu burger et détecte la page active.
 *
 * Variables attendues :
 * @var string $currentPath Chemin de la requête pour détecter la page active
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
            <a href="/accueil"<?= ($currentPath === '/accueil' ? ' class="current"' : '') ?>>Accueil</a>
            <a href="/dashboard"<?= ($currentPath === '/dashboard' ? ' class="current"' : '') ?>>Tableau de bord</a>
            <a href="/profile"<?= ($currentPath === '/profile' ? ' class="current"' : '') ?>>Profil</a>
            <form action="/logout" method="POST" style="display:inline;margin:0">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\Core\Csrf::token(), ENT_QUOTES, 'UTF-8'); ?>">
                <button type="submit" class="nav-login" aria-label="Déconnexion">Déconnexion</button>
            </form>
        </nav>

        <button class="dark-mode-toggle" id="darkModeToggle" aria-label="Activer le mode sombre" title="Mode sombre">
            <span class="icon-sun" aria-hidden="true"></span>
            <span class="icon-moon" aria-hidden="true"></span>
        </button>

        <form action="/logout" method="POST" style="display:inline;margin:0">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\Core\Csrf::token(), ENT_QUOTES, 'UTF-8'); ?>">
            <button type="submit" class="login-btn" aria-label="Déconnexion">Déconnexion</button>
        </form>

        <button class="burger-menu" aria-label="Menu" aria-expanded="false" aria-controls="mainnav">
            <span aria-hidden="true"></span>
            <span aria-hidden="true"></span>
            <span aria-hidden="true"></span>
        </button>
    </div>
</header>