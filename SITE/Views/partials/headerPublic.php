<?php
/**
 * Partial : Header public (Navigation pour visiteurs non authentifiés)
 *
 * Ce composant constitue l'en-tête de navigation de l'application DashMed pour
 * les visiteurs non connectés. Il offre une navigation structurée vers les pages
 * publiques, un système de thème clair/sombre, et une interface responsive avec
 * menu burger pour les appareils mobiles. Le header détecte automatiquement la
 * page courante pour appliquer une classe CSS "current" sur le lien actif.
 *
 * Fonctionnalités :
 * - Affichage du logo et du nom de marque DashMed
 * - Navigation principale vers les pages publiques (Accueil, Plan du site, Mentions légales)
 * - Détection automatique de la page active (classe "current")
 * - Bouton de connexion visible (desktop) et intégré au menu (mobile)
 * - Toggle de mode sombre/clair avec icônes soleil/lune
 * - Menu burger responsive pour navigation mobile
 * - Attributs ARIA pour accessibilité optimale
 * - Support du thème sombre avec transition fluide
 *
 * Éléments du header :
 * 1. Brand (Logo et nom) :
 *    - Logo DashMed (image .png)
 *    - Nom de marque "DashMed" en texte
 *
 * 2. Navigation principale (mainnav) :
 *    - Accueil (/)
 *    - Plan du site (/map)
 *    - Mentions légales (/mentions-legales ou /legal-notices)
 *    - Connexion (/login) - visible uniquement en mobile dans le menu
 *
 * 3. Contrôles utilisateur :
 *    - Toggle mode sombre (bouton avec icônes soleil/lune)
 *    - Bouton de connexion (visible desktop, masqué mobile)
 *    - Menu burger (visible mobile uniquement)
 *
 * Structure visuelle :
 * - Barre horizontale (topbar) avec container centré
 * - Logo + nom alignés à gauche
 * - Navigation centrale (desktop) ou cachée (mobile)
 * - Contrôles alignés à droite (dark mode, login, burger)
 * - Menu déroulant responsive sur mobile
 *
 * Parcours utilisateur :
 * - Arrivée sur le site → Navigation via header
 * - Clic sur logo → Retour à l'accueil
 * - Clic sur lien de navigation → Accès à la page
 * - Clic sur "Connexion" → Redirection vers /login
 * - Toggle mode sombre → Changement de thème instantané
 * - Mobile : Clic sur burger → Ouverture du menu
 *
 * @package    DashMed
 * @subpackage Views\Partials
 * @category   Frontend
 * @version    1.2.0
 * @since      1.0.0
 * @author     FABRE Alexis
 * @author     GHEUX Théo
 * @author     JACOB Alexandre
 * @author     TAHA CHAOUI Amir
 * @author     UYSUN Ali
 *
 * @see        \SITE\Views\index.php Page d'accueil utilisant ce header
 * @see        \SITE\Views\map.php Page du plan du site
 * @see        \SITE\Views\legal-notices.php Page des mentions légales
 * @see        \SITE\Views\auth\login.php Page de connexion (cible du bouton)
 * @see        \SITE\Views\partials\headerPrivate.php Header pour utilisateurs authentifiés
 *
 * @requires   PHP >= 7.4
 * @requires   $_SERVER['REQUEST_URI'] Variable serveur pour détection de page active
 *
 * Dépendances CSS :
 * @uses /Public/assets/style/header.css Styles du header (topbar, navigation, burger)
 * @uses /Public/assets/style/dark-mode.css Styles du mode sombre et toggle
 *
 * Dépendances JavaScript :
 * @uses /Public/assets/script/header_responsive.js Gestion du menu burger et navigation responsive
 * @uses /Public/assets/script/dark-mode.js Gestion du toggle mode sombre/clair
 *
 * Assets images :
 * @uses /Public/assets/images/logo.png Logo de l'application DashMed
 *
 * Variables PHP :
 * @var string $currentPath Chemin de l'URL courante (extrait de REQUEST_URI)
 *
 * Classes CSS principales :
 * @class topbar           Conteneur principal du header
 * @class container        Conteneur centré avec largeur max
 * @class brand            Groupe logo + nom de marque
 * @class logo             Image du logo DashMed
 * @class brand-name       Texte "DashMed" à côté du logo
 * @class mainnav          Navigation principale
 * @class current          Classe appliquée au lien de la page active
 * @class dark-mode-toggle Bouton de bascule mode sombre
 * @class icon-sun         Icône soleil (mode clair)
 * @class icon-moon        Icône lune (mode sombre)
 * @class login-btn        Bouton de connexion (desktop)
 * @class nav-login        Lien de connexion dans le menu (mobile)
 * @class burger-menu      Bouton menu burger (mobile)
 *
 * Détection de page active :
 * - Extrait le chemin de l'URL via parse_url()
 * - Compare $currentPath avec les routes définies
 * - Applique class="current" sur le lien correspondant
 * - Gère les alias (/mentions-legales et /legal-notices)
 *
 * Routes surveillées :
 * @route / Page d'accueil
 * @route /map Plan du site
 * @route /mentions-legales Mentions légales (alias principal)
 * @route /legal-notices Mentions légales (alias alternatif)
 * @route /login Page de connexion
 *
 *
 * Responsive Design :
 * - Desktop (>768px) :
 *   - Navigation horizontale visible
 *   - Bouton "Connexion" visible à droite
 *   - Menu burger masqué
 *
 *
 * Comportements JavaScript :
 * - Clic sur burger → Toggle classe "active" sur navigation
 * - Clic sur burger → aria-expanded="true/false"
 * - Clic sur burger → aria-hidden="false/true" sur navigation
 * - Clic sur dark-mode-toggle → Bascule attribut [data-theme="dark"]
 * - Stockage préférence mode sombre en localStorage
 * - Application automatique du thème au chargement
 *
 * Différences avec headerPrivate.php :
 * - Pas de lien vers le dashboard
 * - Présence du bouton "Connexion"
 * - Navigation publique uniquement
 * - Pas de bouton de déconnexion
 */

// ============================================================================
// DÉTECTION DE LA PAGE COURANTE
// ============================================================================

/**
 * Extraction du chemin de l'URL courante pour détection de page active.
 *
 * Utilise parse_url() pour extraire uniquement le chemin (sans query string ni fragment).
 * Ce chemin est ensuite comparé aux routes pour appliquer la classe "current".
 *
 * @var string $currentPath Chemin de l'URL courante (ex: "/", "/map", "/mentions-legales")
 *
 * @example
 * URL complète : https://dashmed.com/map?param=value#section
 * $currentPath : /map
 *
 * @security Protection contre injection via parse_url()
 */
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
?>

<!--
    Header public : Navigation pour visiteurs non authentifiés

    Structure :
    - Brand : Logo + Nom DashMed
    - Navigation : Liens vers pages publiques
    - Controls : Dark mode toggle + Login + Burger menu

    Responsive : Menu burger activé en mobile (<768px)
-->
<header class="topbar">
    <div class="container">

        <div class="brand">
            <img class="logo" src="/assets/images/logo.png" alt="Logo DashMed">
            <span class="brand-name">DashMed</span>
        </div>


        <nav id="mainnav" class="mainnav" aria-label="Navigation principale" aria-hidden="false">
            <!-- Lien Accueil : Active si $currentPath === '/' -->
            <a href="/"<?= ($currentPath === '/' ? ' class="current"' : '') ?>>Accueil</a>

            <!-- Lien Plan du site : Active si $currentPath === '/map' -->
            <a href="/map"<?= ($currentPath === '/map' ? ' class="current"' : '') ?>>Plan du site</a>

            <!--
                Lien Mentions légales : Active si /mentions-legales OU /legal-notices
                Gère les deux alias pour compatibilité
            -->
            <a href="/mentions-legales"<?= ($currentPath === '/mentions-legales' || $currentPath === '/legal-notices' ? ' class="current"' : '') ?>>Mentions légales</a>

            <a href="/login" class="nav-login">Connexion</a>
        </nav>

        <!--
            Toggle mode sombre/clair

            Icônes :
            - icon-sun : Affichée en mode sombre (pour passer en clair)
            - icon-moon : Affichée en mode clair (pour passer en sombre)

            Comportement JS :
            - Clic → Bascule [data-theme="dark"] sur <html>
            - Sauvegarde préférence en localStorage
        -->
        <button class="dark-mode-toggle" id="darkModeToggle" aria-label="Activer le mode sombre" title="Mode sombre">
            <span class="icon-sun"></span>
            <span class="icon-moon"></span>
        </button>

        <!--
            Bouton de connexion (desktop uniquement)
            Masqué sur mobile via CSS (.login-btn { display: none })
        -->
        <a href="/login" class="login-btn">Connexion</a>


        <button class="burger-menu" aria-label="Menu" aria-expanded="false" aria-controls="mainnav">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </div>
</header>
