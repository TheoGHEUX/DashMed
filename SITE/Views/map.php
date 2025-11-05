<?php
/**
 * Vue : Page du plan du site (Site Map)
 *
 * Cette page présente l'architecture complète et structurée de l'application DashMed.
 * Elle liste l'ensemble des pages accessibles de manière hiérarchique pour faciliter
 * la navigation des utilisateurs et améliorer le référencement (SEO). Le plan du site
 * est organisé par catégories et niveaux pour une compréhension intuitive.
 *
 * Fonctionnalités :
 * - Liste exhaustive des pages publiques de l'application
 * - Navigation structurée en niveaux hiérarchiques (niveau 1, niveau 2)
 * - Accessibilité optimisée avec balises sémantiques et ARIA
 * - Mise en page claire et responsive
 * - Section d'aide utilisateur (tips)
 * - Support du mode sombre
 *
 * Sections de la page :
 * 1. En-tête :
 *    - Titre principal "Plan du site"
 *    - Description courte "Toutes les pages disponibles sur DashMed"
 *
 * 2. Navigation structurée (sitemap) :
 *    Niveau 1 - Pages principales :
 *    - Accueil (/)
 *    - Espace utilisateur (section)
 *    - Informations (section)
 *
 *    Niveau 2 - Sous-pages :
 *    a) Espace utilisateur :
 *       - Inscription (/register)
 *       - Connexion (/login)
 *       - Mot de passe oublié (/forgotten-password)
 *
 *    b) Informations :
 *       - Mentions légales (/mentions-legales)
 *       - Plan du site (/map)
 *
 * 3. Section Tips :
 *    - Encadré informatif pour guider l'utilisateur
 *    - Explication de l'utilité du plan du site
 *
 * Structure visuelle :
 * - Header public (navigation pour visiteurs)
 * - Container principal avec navigation hiérarchique
 * - Liste à puces multi-niveaux (ul > li)
 * - Encadré tips stylisé
 * - Footer commun avec liens complémentaires
 *
 * Public cible :
 * - Visiteurs non connectés cherchant une page spécifique
 * - Nouveaux utilisateurs découvrant l'application
 * - Utilisateurs perdus dans la navigation
 * - Robots d'indexation (crawlers SEO)
 * - Utilisateurs avec technologies d'assistance (lecteurs d'écran)
 *
 * Parcours utilisateur :
 * - Accès depuis le footer (lien "Plan du site")
 * - Exploration des sections niveau 1
 * - Navigation vers sous-pages niveau 2
 * - Clic direct sur le lien souhaité
 *
 * @package    DashMed
 * @subpackage Views
 * @category   Frontend
 * @version    1.1.0
 * @since      1.0.0
 * @author     FABRE Alexis
 * @author     GHEUX Théo
 * @author     JACOB Alexandre
 * @author     TAHA CHAOUI Amir
 * @author     UYSUN Ali
 * @copyright  2025 DashMed Team
 * @license    Proprietary
 *
 * @see        \SITE\Controllers\SiteMapController.php Contrôleur gérant cette vue
 * @see        \SITE\Views\partials\footer.php Footer contenant le lien vers le plan du site
 * @see        \SITE\Views\partials\headerPublic.php Header pour visiteurs non authentifiés
 * @see        \SITE\Views\index.php Page d'accueil
 * @see        \SITE\Views\auth\register.php Page d'inscription
 * @see        \SITE\Views\auth\login.php Page de connexion
 * @see        \SITE\Views\auth\forgotten-password.php Page mot de passe oublié
 * @see        \SITE\Views\legal-notices.php Page des mentions légales
 *
 * @requires   PHP >= 7.4
 *
 * Dépendances CSS :
 * @uses /Public/assets/style/map.css Styles du plan du site (navigation hiérarchique, tips)
 *
 * Dépendances JavaScript :
 * @uses /Public/assets/script/header_responsive.js Gestion du menu responsive du header
 *
 * Variables de template :
 * @var string $pageTitle       Titre de la page, par défaut "Plan du site"
 * @var string $pageDescription Meta description SEO pour le plan du site
 * @var array  $pageStyles      Chemins des CSS : ["/assets/style/map.css"]
 * @var array  $pageScripts     Chemins des scripts JS : ["/assets/script/header_responsive.js"]
 *
 * Structure de navigation :
 * - <nav class="sitemap"> : Conteneur principal de la navigation
 * - <ul class="level-1"> : Liste des sections principales
 * - <ul class="level-2"> : Liste des sous-pages
 * - <span> : Titres de sections non cliquables
 * - <a> : Liens vers les pages accessibles
 *
 * Pages référencées :
 * @link / Page d'accueil
 * @link /register Page d'inscription
 * @link /login Page de connexion
 * @link /forgotten-password Page mot de passe oublié
 * @link /mentions-legales Page des mentions légales
 * @link /map Page du plan du site (page actuelle)
 *
 * SEO & Marketing :
 * - Mots-clés : plan du site, navigation, pages DashMed, sitemap
 * - Amélioration du crawling par les moteurs de recherche
 * - Facilite l'indexation de toutes les pages publiques
 * - Réduit le taux de rebond en guidant l'utilisateur
 * - Améliore l'expérience utilisateur (UX) globale
 *
 * Accessibilité :
 * - Balise <nav> avec attribut aria-label="Plan du site"
 * - Structure sémantique <ul> / <li> pour hiérarchie
 * - Titres non cliquables en <span> (non interactifs)
 */
?>
<!doctype html>
<html lang="fr">
<?php
// ============================================================================
// CONFIGURATION : Variables du template
// ============================================================================

/**
 * Titre de la page affiché dans la balise <title> et l'onglet du navigateur.
 * Optimisé pour le SEO et l'identification de la page.
 *
 * @var string $pageTitle Valeur par défaut : "Plan du site"
 */
$pageTitle = "Plan du site";

/**
 * Description de la page pour les moteurs de recherche (SEO).
 * Résumé concis du contenu et de l'utilité de la page.
 *
 * @var string $pageDescription
 */
$pageDescription = "Plan du site de DashMed";

/**
 * Liste des feuilles de style CSS spécifiques à cette page.
 * Contient les styles pour la navigation hiérarchique et les tips.
 *
 * @var array<int, string> $pageStyles Chemins relatifs depuis /Public
 */
$pageStyles = ["/assets/style/map.css"];

/**
 * Liste des scripts JavaScript spécifiques à cette page.
 * Gère le menu responsive du header public.
 *
 * @var array<int, string> $pageScripts Chemins relatifs depuis /Public
 */
$pageScripts = ["/assets/script/header_responsive.js"];

include __DIR__ . '/partials/head.php';
?>
<body>

<?php include __DIR__ . '/partials/headerPublic.php'; ?>

<main class="content">
    <div class="container">
        <h1>Plan du site</h1>
        <p class="muted">Toutes les pages disponibles sur DashMed.</p>

        <nav class="sitemap" aria-label="Plan du site">
            <ul class="level-1">
                <!-- Page d'accueil -->
                <li>
                    <a href="/">Accueil</a>
                </li>

                <!-- Section : Espace utilisateur -->
                <li>
                    <span>Espace utilisateur</span>
                    <ul class="level-2">
                        <li><a href="/register">Inscription</a></li>
                        <li><a href="/login">Connexion</a></li>
                        <li><a href="/forgotten-password">Mot de passe oublié</a></li>
                    </ul>
                </li>

                <!-- Section : Informations -->
                <li>
                    <span>Informations</span>
                    <ul class="level-2">
                        <li><a href="/mentions-legales">Mentions légales</a></li>
                        <li><a href="/map">Plan du site</a></li>
                    </ul>
                </li>
            </ul>
        </nav>

        <div class="tips">
            Trouvez toutes les pages du site depuis ce tableau pour naviguer plus facilement !
        </div>
    </div>
</main>

<?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>