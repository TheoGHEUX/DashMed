<?php
/**
 * Vue : Page d'accueil publique (Home)
 *
 * Cette page constitue la vitrine de l'application DashMed. C'est le premier point
 * de contact pour les visiteurs non authentifiés. Elle présente les fonctionnalités
 * principales du service et incite à l'inscription ou à la connexion.
 *
 * Fonctionnalités :
 * - Section hero avec présentation du service et image illustrative
 * - Call-to-action (CTA) pour inscription et connexion
 * - Section features présentant 3 avantages clés du service
 * - Design responsive et moderne
 * - Accessibilité optimisée (balises alt, structure sémantique)
 *
 * Sections de la page :
 * 1. Hero :
 *    - Titre principal accrocheur
 *    - Image du tableau de bord en démonstration
 *    - Description du service (lead)
 *    - Deux boutons CTA (inscription primaire, connexion secondaire)
 *
 * 2. Features (Avantages) :
 *    - Suivi clair : Graphiques et indicateurs lisibles
 *    - Sécurité : Chiffrement et conformité des données
 *    - Personnalisable : Adaptation aux besoins utilisateurs
 *
 * Structure visuelle :
 * - Header public (avec menu de navigation)
 * - Section hero avec image en arrière-plan
 * - Section features avec 3 cartes illustrées
 * - Footer commun
 *
 * Public cible :
 * - Visiteurs non connectés
 * - Nouveaux utilisateurs potentiels
 * - Professionnels de santé recherchant une solution de suivi
 *
 * Parcours utilisateur :
 * - Arrivée sur la page → Découverte du service → CTA inscription/connexion
 * - Scroll pour en savoir plus → Features → Footer avec liens complémentaires
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
 * @see        \SITE\Controllers\HomeController.php Contrôleur gérant cette vue
 * @see        \SITE\Views\auth\register.php Page d'inscription (cible du CTA principal)
 * @see        \SITE\Views\auth\login.php Page de connexion (cible du CTA secondaire)
 * @see        \SITE\Views\partials\headerPublic.php Header pour visiteurs non authentifiés
 *
 * @requires   PHP >= 7.4
 *
 * Dépendances CSS :
 * @uses /Public/assets/style/index.css Styles de la page d'accueil (hero, features, CTA)
 *
 * Dépendances JavaScript :
 * @uses /Public/assets/script/header_responsive.js Gestion du menu responsive
 *
 * Assets images :
 * @uses /Public/assets/images/dashboard.webp Image de démonstration du tableau de bord
 * @uses /Public/assets/images/suivi.webp Illustration du suivi médical
 * @uses /Public/assets/images/securite.webp Illustration de la sécurité
 * @uses /Public/assets/images/personnalisable.webp Illustration de la personnalisation
 *
 * Variables de template :
 * @var string $pageTitle       Titre de la page (affiché dans <title>), par défaut "Accueil"
 * @var string $pageDescription Meta description pour le SEO
 * @var array  $pageStyles      Chemins des feuilles de style à inclure
 * @var array  $pageScripts     Chemins des scripts JavaScript à inclure
 *
 * SEO & Marketing :
 * - Mots-clés ciblés : tableau de bord santé, suivi médical, données de santé
 * - Proposition de valeur claire dans le hero
 * - Preuves sociales via les features (sécurité, conformité)
 * - CTA bien visibles et orientés conversion
 *
 * Accessibilité :
 * - Textes alternatifs descriptifs sur toutes les images
 * - Structure sémantique (section, h1, h2)
 * - Contraste des boutons conforme WCAG 2.1
 * - Navigation au clavier fonctionnelle
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
 * Peut être surchargé par le contrôleur via la variable transmise.
 *
 * @var string $pageTitle Valeur par défaut : "Accueil"
 */
$pageTitle = $pageTitle ?? "Accueil";

/**
 * Description de la page pour les moteurs de recherche (SEO).
 * Optimisée pour le référencement avec mots-clés pertinents.
 * Peut être surchargée par le contrôleur.
 *
 * @var string $pageDescription
 */
$pageDescription = $pageDescription ?? "Page d'accueil de DashMed : votre tableau de bord santé simple et moderne pour la médecine";

/**
 * Liste des feuilles de style CSS spécifiques à cette page.
 * Contient les styles pour le hero, les features et les CTA.
 * Peut être surchargée par le contrôleur.
 *
 * @var array<int, string> $pageStyles Chemins relatifs depuis /Public
 */
$pageStyles = $pageStyles ?? ["/assets/style/index.css"];

/**
 * Liste des scripts JavaScript spécifiques à cette page.
 * Gère principalement le menu responsive du header public.
 *
 * @var array<int, string> $pageScripts Chemins relatifs depuis /Public
 */
$pageScripts = ["/assets/script/header_responsive.js"];

include __DIR__ . '/partials/head.php';
?>
<body>
<?php include __DIR__ . '/partials/headerPublic.php'; ?>
<main>
    <section class="hero">
        <h1>Votre tableau de bord santé, simple et moderne</h1>
        <div class="cadre-hero">
            <img src="/assets/images/dashboard.webp" alt="Tableau de bord médical moderne" class="hero-bg" />
            <div class="hero-content">
                <p class="lead">
                    DashMed centralise vos données de santé pour mieux suivre vos objectifs,
                    visualiser vos progrès et rester informé.
                </p>
                <div class="cta">
                    <a class="btn btn-primary" href="/register">S'inscrire</a>
                    <a class="btn btn-ghost" href="/login">Se connecter</a>
                </div>
            </div>
        </div>
    </section>

    <section class="features">
        <h1>Pourquoi choisir DashMed ?</h1>
        <div class="cadre-features">
            <img src="/assets/images/suivi.webp" alt="image representant un graphique de suivi" />
            <div class="suivi-content">
                <h2>Suivi clair</h2>
                <p>Des indicateurs lisibles et des graphiques pour comprendre vos mesures en un coup d'œil.</p>
            </div>
        </div>

        <div class="cadre-features">
            <img src="/assets/images/securite.webp" alt="image representant un cadenas sur un serveur" />
            <div class="securite-content">
                <h2>Sécurité</h2>
                <p>Vos données sont chiffrées et hébergées sur des serveurs conformes aux standards.</p>
            </div>
        </div>

        <div class="cadre-features">
            <img src="/assets/images/personnalisable.webp" alt="image de personnalisation" />
            <div class="personnalisable-content">
                <h2>Personnalisable</h2>
                <p>Adaptez vos tableaux, vos unités et vos objectifs selon votre pratique.</p>
            </div>
        </div>
    </section>
</main>

<?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>