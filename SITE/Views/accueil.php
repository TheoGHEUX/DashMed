<?php
/**
 * Vue : Page d'accueil utilisateur (Accueil)
 *
 * Cette page constitue le point d'entrée principal pour les utilisateurs authentifiés.
 * Elle affiche une bannière de bienvenue et un lien vers le tableau de bord complet.
 *
 * Fonctionnalités :
 * - Vérification de l'authentification utilisateur (session)
 * - Protection CSRF pour les formulaires
 * - Affichage de la bannière d'accueil avec message personnalisé
 * - Redirection rapide vers le tableau de bord
 *
 * Sécurité :
 * - Contrôle d'accès : nécessite une session utilisateur active
 * - Token CSRF généré pour sécuriser les interactions futures
 * - Redirection automatique vers /login si non authentifié
 *
 * Structure de la page :
 * - Header privé (avec menu utilisateur connecté)
 * - Section bannière avec titre et description
 * - Carte cliquable vers le dashboard
 * - Footer commun
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
 *
 * @see        \Core\Csrf Pour la gestion des tokens CSRF
 * @see        /SITE/Views/dashboard.php Page du tableau de bord complet
 * @see        /SITE/Views/partials/headerPrivate.php Header pour utilisateurs authentifiés
 *
 * @requires   PHP >= 7.4
 * @requires   Session active avec $_SESSION['user']
 *
 * @global array $_SESSION Données de session pour l'authentification
 *
 * Dépendances CSS :
 * @uses /Public/assets/style/accueil.css Styles spécifiques à la page d'accueil
 *
 * Dépendances JavaScript :
 * @uses /Public/assets/script/header_responsive.js Gestion du menu responsive
 *
 * Variables de template :
 * @var string $pageTitle       Titre de la page (affiché dans <title>)
 * @var string $pageDescription Meta description pour le SEO
 * @var array  $pageStyles      Chemins des feuilles de style à inclure
 * @var array  $pageScripts     Chemins des scripts JavaScript à inclure
 * @var string $csrf_token      Token CSRF pour la protection des formulaires
 */

// ============================================================================
// SÉCURITÉ : Génération du token CSRF
// ============================================================================

/**
 * Token CSRF pour la protection contre les attaques Cross-Site Request Forgery.
 * Ce token doit être inclus dans tous les formulaires de la page.
 *
 * @var string $csrf_token Token unique généré pour la session courante
 * @see \Core\Csrf::token() Méthode de génération du token
 */
$csrf_token = \Core\Csrf::token();

// ============================================================================
// CONTRÔLE D'ACCÈS : Vérification de l'authentification
// ============================================================================

/**
 * Vérifie si l'utilisateur est authentifié.
 * Si $_SESSION['user'] est vide ou non défini, redirige vers la page de connexion.
 * Cette protection empêche l'accès non autorisé aux pages privées.
 *
 * @uses $_SESSION['user'] Données de l'utilisateur connecté
 */
if (empty($_SESSION['user'])) {
    header('Location: /login');
    exit;
}

// ============================================================================
// CONFIGURATION : Variables du template
// ============================================================================

/**
 * Titre de la page affiché dans la balise <title> et l'onglet du navigateur.
 *
 * @var string $pageTitle
 */
$pageTitle = "Accueil";

/**
 * Description de la page pour les moteurs de recherche (SEO).
 * Affichée dans la balise <meta name="description">.
 *
 * @var string $pageDescription
 */
$pageDescription = "Page d'accueil accessible une fois connecté, espace pour voir l'activité et les informations des médecins";

/**
 * Liste des feuilles de style CSS spécifiques à cette page.
 * Ces fichiers seront inclus en plus des styles globaux.
 *
 * @var array<int, string> $pageStyles Chemins relatifs depuis /Public
 */
$pageStyles = [
        "/assets/style/accueil.css"
];

/**
 * Liste des scripts JavaScript spécifiques à cette page.
 *
 * @var array<int, string> $pageScripts Chemins relatifs depuis /Public
 */
$pageScripts = [
        "/assets/script/header_responsive.js"];
?>
<!doctype html>
<html lang="fr">
<?php include __DIR__ . '/partials/head.php'; ?>

<body>
<?php include __DIR__ . '/partials/headerPrivate.php'; ?>
<main>
    <div class="accueil-container">
        <!-- Phrase d'accroche et Dashboard -->
        <section class="dashboard-banner">
            <div class="banner-content">
                <h1 class="welcome-title" style="color: #0fb0c0 !important;">Bienvenue sur DashMed</h1>
                <p>Votre plateforme médicale pour une gestion hospitalière efficace et sécurisée</p>
                <a href="/dashboard" class="dashboard-card">
                    <div class="card-icon">📊</div>
                    <div class="card-text">
                        <h3>Tableau de bord</h3>
                        <span>Voir toutes mes données</span>
                    </div>
                    <div class="card-arrow">→</div>
                </a>
            </div>
        </section>
    </div>
</main>

<?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>