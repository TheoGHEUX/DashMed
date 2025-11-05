<?php
/**
 * Vue : Page d'erreur 404 (Page non trouvée)
 *
 * Cette page constitue la réponse standard de l'application DashMed lorsqu'une ressource
 * demandée n'existe pas ou n'est plus accessible. Elle offre une expérience utilisateur
 * claire et rassurante en cas d'erreur de navigation, avec des options de redirection
 * vers les pages principales. Le design est cohérent avec l'identité visuelle de
 * l'application et propose des actions pour guider l'utilisateur.
 *
 * Fonctionnalités :
 * - Affichage clair du code erreur 404
 * - Message explicatif pour l'utilisateur
 * - Suggestions de navigation (retour accueil, tableau de bord)
 * - Responsive et adapté à tous les écrans
 * - Support du mode sombre
 * - Header public pour contexte de navigation
 * - Footer commun pour cohérence visuelle
 *
 * Sections de la page :
 * 1. Container d'erreur :
 *    - Code erreur "404" en grand (élément visuel fort)
 *    - Titre principal "Page non trouvée"
 *    - Message explicatif et rassurant
 *
 * 2. Actions proposées :
 *    - Bouton primaire : Retour à l'accueil (/)
 *    - Bouton secondaire : Tableau de bord (/dashboard)
 *
 * Structure visuelle :
 * - Header public (navigation de secours)
 * - Container centré verticalement et horizontalement
 * - Code 404 en grande taille (typographie forte)
 * - Titre et message empilés verticalement
 * - Boutons d'action alignés horizontalement (ou verticalement sur mobile)
 * - Footer commun en bas de page
 *
 * Public cible :
 * - Utilisateurs ayant suivi un lien brisé
 * - Visiteurs ayant fait une erreur de frappe dans l'URL
 * - Utilisateurs cherchant une page supprimée ou déplacée
 *
 * Parcours utilisateur :
 * - Tentative d'accès à une URL inexistante
 * - Affichage de la page 404 avec code HTTP approprié
 * - Lecture du message d'erreur
 * - Clic sur "Retour à l'accueil" → Redirection vers / accueil
 * - OU Clic sur "Tableau de bord" → Redirection vers /dashboard
 * - Navigation alternative via header
 *
 * @package    DashMed
 * @subpackage Views\Errors
 * @category   Frontend
 * @version    1.1.0
 * @since      1.0.0
 * @author     FABRE Alexis
 * @author     GHEUX Théo
 * @author     JACOB Alexandre
 * @author     TAHA CHAOUI Amir
 * @author     UYSUN Ali
 *
 * @see        \SITE\Controllers\ErrorController.php Contrôleur gérant les erreurs
 * @see        \SITE\Views\errors\500.php Page d'erreur 500 (erreur serveur)
 * @see        \SITE\Views\index.php Page d'accueil (cible du bouton primaire)
 * @see        \SITE\Views\dashboard.php Tableau de bord (cible du bouton secondaire)
 * @see        \SITE\Views\partials\headerPublic.php Header pour navigation de secours
 * @see        \SITE\Views\partials\head.php Section <head> commune
 * @see        \SITE\Views\partials\footer.php Footer commun
 *
 * @requires   PHP >= 7.4
 * @requires   Serveur web configuré pour rediriger les erreurs 404 vers ce fichier
 *
 * Dépendances CSS :
 * @uses /Public/assets/style/404.css Styles de la page d'erreur (centrage, typographie, boutons)
 * @uses /Public/assets/style/body_main_container.css Structure générale (via head.php)
 * @uses /Public/assets/style/header.css Styles du header (via head.php)
 * @uses /Public/assets/style/footer.css Styles du footer (via head.php)
 * @uses /Public/assets/style/dark-mode.css Support du mode sombre (via head.php)
 *
 * Dépendances JavaScript :
 * @uses /Public/assets/script/header_responsive.js Menu burger responsive (via head.php)
 * @uses /Public/assets/script/dark-mode.js Gestion du mode sombre (via head.php)
 *
 * Variables de template :
 * @var string $pageTitle       Titre de la page, par défaut "Page non trouvée - Erreur 404"
 * @var string $pageDescription Meta description SEO, par défaut "La page que vous recherchez n'existe pas."
 * @var array  $pageStyles      Chemins des CSS : ["/assets/style/404.css"]
 * @var array  $pageScripts     Chemins des scripts JS (vide pour cette page)
 *
 * Classes CSS principales :
 * @class error-container Conteneur principal centré (flexbox vertical)
 * @class error-content   Bloc de contenu de l'erreur
 * @class error-code      Code "404" en grande taille (élément visuel fort)
 * @class error-title     Titre "Page non trouvée"
 * @class error-message   Message explicatif pour l'utilisateur
 * @class error-actions   Conteneur des boutons d'action
 * @class btn             Classe de base pour les boutons
 * @class btn-primary     Bouton principal (retour accueil)
 * @class btn-secondary   Bouton secondaire (tableau de bord)
 *
 * Actions disponibles :
 * @action Retour à l'accueil (/) - Redirection vers la page d'accueil publique
 * @action Tableau de bord (/dashboard) - Redirection vers le dashboard (si authentifié)
 *
 * Accessibilité :
 * - Structure sémantique (<main>, <h1>, <p>)
 * - Hiérarchie des titres respectée (h1 unique)
 * - Boutons avec texte descriptif
 * - Taille de police lisible
 *
 * Responsive Design :
 * - Desktop :
 *   - Code 404 en grande taille
 *   - Boutons alignés horizontalement
 *   - Espacement confortable
 *
 * - Mobile :
 *   - Code 404 adapté
 *   - Boutons empilés verticalement
 *   - Padding réduit pour petits écrans
 *   - Texte centré
 *
 * Sécurité :
 * - Pas d'affichage de l'URL demandée
 * - Pas d'informations sensibles sur la structure du site
 * - Pas de stacktrace ou détails techniques
 * - Protection contre l'énumération de ressources

 */

/**
 * Variables dynamiques transmises depuis le contrôleur ou définies par défaut.
 *
 * Ces variables personnalisent le contenu de la page d'erreur et sont injectées
 * dans le partial head.php pour la génération du <head>.
 *
 * Si le contrôleur ne les définit pas, des valeurs par défaut sont utilisées
 * via l'opérateur null coalescent (??).
 *
 * @var string $pageTitle       Titre affiché dans l'onglet du navigateur
 * @var string $pageDescription Meta description pour SEO (devrait être noindex)
 * @var array  $pageStyles      Liste des feuilles de style CSS spécifiques
 * @var array  $pageScripts     Liste des scripts JS spécifiques (vide ici)
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
 * Inclut "Erreur 404" pour clarté et contexte.
 *
 * @var string $pageTitle Valeur par défaut : "Page non trouvée - Erreur 404"
 */
$pageTitle = $pageTitle ?? "Page non trouvée - Erreur 404";

/**
 * Description de la page pour les métadonnées SEO.
 * Note : Cette page devrait être en noindex (non indexée par les moteurs).
 *
 * @var string $pageDescription Valeur par défaut : "La page que vous recherchez n'existe pas."
 */
$pageDescription = $pageDescription ?? "La page que vous recherchez n'existe pas.";

/**
 * Liste des feuilles de style CSS spécifiques à cette page d'erreur.
 * Contient les styles pour le centrage, la typographie et les boutons.
 *
 * @var array<int, string> $pageStyles Chemins relatifs depuis /Public
 */
$pageStyles = $pageStyles ?? ["/assets/style/404.css"];

/**
 * Liste des scripts JavaScript spécifiques à cette page.
 * Aucun script nécessaire pour cette page statique d'erreur.
 *
 * @var array<int, string> $pageScripts Tableau vide
 */
$pageScripts = $pageScripts ?? [];

// Inclusion du partial <head> avec variables configurées
include __DIR__ . '/../partials/head.php';
?>

<body>

<?php
/**
 * Inclusion du header public pour contexte de navigation.
 *
 * Permet à l'utilisateur de naviguer via le menu même en cas d'erreur.
 * Offre une alternative de navigation en cas de page inexistante.
 *
 * @uses \SITE\Views\partials\headerPublic.php
 */
include __DIR__ . '/../partials/headerPublic.php';
?>

<main>
    <div class="error-container">
        <div class="error-content">
            <div class="error-code">404</div>
            <h1 class="error-title">Page non trouvée</h1>
            <p class="error-message">
                Désolé, la page que vous recherchez n'existe pas ou a été déplacée.
            </p>
            <div class="error-actions">
                <a href="/" class="btn btn-primary">Retour à l'accueil</a>
                <a href="/dashboard" class="btn btn-secondary">Tableau de bord</a>
            </div>
        </div>
    </div>
</main>

<?php
/**
 * Inclusion du footer commun pour cohérence visuelle.
 *
 * Maintient la structure générale de l'application même en cas d'erreur.
 * Affiche le copyright et éventuels liens utiles.
 *
 * @uses \SITE\Views\partials\footer.php
 */
include __DIR__ . '/../partials/footer.php';
?>
</body>
</html>