<?php
/**
 * Partial : Header pour visiteurs non connectés
 * @package Views
 */

// 1. On importe la classe avec le BON namespace (pas Core\Domain...)
use Domain\Services\NavigationService;

// 2. On instancie le service
$nav = new NavigationService();

?>
<header class="topbar">
    <div class="container">
        <div class="brand">
            <img class="logo" src="/assets/images/logo.png" alt="Logo DashMed">
            <span class="brand-name">DashMed</span>
        </div>

        <nav id="mainnav" class="mainnav" aria-label="Navigation principale">
            <!-- 3. On utilise le service pour savoir si le lien est actif -->
            <a href="/"<?= $nav->activeClass('/') ?>>Accueil</a>
            <a href="/map"<?= $nav->activeClass('/map') ?>>Plan du site</a>
            <a href="/mentions-legales"<?= $nav->activeClass('/mentions-legales') ?>>Mentions légales</a>
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