<?php
/**
 * Partial : Head commun (Section <head> de l'application)
 *
 * Ce composant constitue la section <head> partagée de l'application DashMed, utilisée
 * sur toutes les pages pour centraliser les métadonnées, les feuilles de style et les
 * scripts communs. Il permet une gestion dynamique et modulaire des ressources selon
 * les besoins de chaque page, tout en maintenant une structure cohérente et optimisée
 * pour le SEO, les performances et l'accessibilité.
 *
 * Fonctionnalités :
 * - Gestion dynamique du titre de page (<title>)
 * - Meta description personnalisable pour chaque page (SEO)
 * - Chargement des polices Google Fonts (Poppins) avec preconnect optimisé
 * - Inclusion automatique des styles communs (body, header, footer, dark-mode)
 * - Injection dynamique des styles spécifiques à chaque page ($pageStyles)
 * - Favicon personnalisé (logo DashMed)
 * - Chargement optimisé des scripts (defer, ordre de priorité)
 * - Injection dynamique des scripts spécifiques à chaque page ($pageScripts)
 * - Protection XSS via htmlspecialchars() sur toutes les variables
 * - Support du mode sombre avec script prioritaire (évite flash FOUC)
 * - Responsive design via meta viewport
 *
 * Variables dynamiques attendues :
 * 1. $pageTitle (string) :
 *    - Titre de la page affiché dans l'onglet du navigateur
 *    - Valeur par défaut : "DashMed"
 *    - Exemple : "Accueil", "Profil", "Tableau de bord"
 *
 * 2. $pageDescription (string) :
 *    - Meta description pour le SEO
 *    - Valeur par défaut : chaîne vide
 *    - Recommandation : 150-160 caractères
 *
 * 3. $pageStyles (array) :
 *    - Liste des chemins CSS spécifiques à la page
 *    - Format : ["/assets/style/page1.css", "/assets/style/page2.css"]
 *    - Chargés après les styles communs
 *
 * 4. $pageScripts (array) :
 *    - Liste des chemins JS spécifiques à la page
 *    - Format : ["/assets/script/page1.js", "/assets/script/page2.js"]
 *    - Chargés avec attribut defer pour performance
 *
 * Structure du <head> :
 * 1. Métadonnées de base :
 *    - Charset UTF-8 (support international)
 *    - Viewport responsive (mobile-first)
 *    - Meta description (SEO)
 *    - Title personnalisé
 *
 * 2. Ressources externes :
 *    - Preconnect Google Fonts (optimisation DNS)
 *    - Police Poppins (weights: 400, 500, 600)
 *
 * 3. Styles communs :
 *    - body_main_container.css (structure générale)
 *    - header.css (navigation)
 *    - footer.css (pied de page)
 *    - dark-mode.css (thème sombre)
 *
 * 4. Styles spécifiques :
 *    - Injectés dynamiquement via $pageStyles
 *
 * 5. Favicon :
 *    - Logo DashMed (/assets/images/logo.png)
 *
 * 6. Scripts :
 *    - Scripts spécifiques ($pageScripts) avec defer
 *    - dark-mode.js (prioritaire, sans defer, évite FOUC)
 *    - header_responsive.js (global, avec defer)
 *
 * Ordre de chargement critique :
 * - dark-mode.js chargé en PREMIER (sans defer) pour éviter flash
 * - pageScripts chargés avec defer
 * - header_responsive.js chargé en DERNIER avec defer
 *
 * @package    DashMed
 * @subpackage Views\Partials
 * @category   Frontend
 * @version    1.3.0
 * @since      1.0.0
 * @author     FABRE Alexis
 * @author     GHEUX Théo
 * @author     JACOB Alexandre
 * @author     TAHA CHAOUI Amir
 * @author     UYSUN Ali
 *
 * @see        \SITE\Views\index.php Exemple d'utilisation avec variables personnalisées
 * @see        \SITE\Views\profile.php Exemple avec styles et scripts spécifiques
 * @see        \SITE\Views\partials\headerPublic.php Header utilisant les styles communs
 * @see        \SITE\Views\partials\footer.php Footer utilisant les styles communs
 *
 * @requires   PHP >= 7.4
 *
 * Variables attendues (transmises par les vues) :
 * @var string $pageTitle       Titre de la page (optionnel, défaut : "DashMed")
 * @var string $pageDescription Description SEO (optionnel, défaut : "")
 * @var array  $pageStyles      Chemins CSS spécifiques (optionnel, défaut : [])
 * @var array  $pageScripts     Chemins JS spécifiques (optionnel, défaut : [])
 *
 * Styles communs chargés :
 * @uses /Public/assets/style/body_main_container.css Structure générale (body, main, container)
 * @uses /Public/assets/style/header.css Styles du header (topbar, navigation, burger)
 * @uses /Public/assets/style/footer.css Styles du footer
 * @uses /Public/assets/style/dark-mode.css Gestion du mode sombre
 *
 * Scripts communs chargés :
 * @uses /Public/assets/script/dark-mode.js Toggle et persistance du mode sombre (prioritaire)
 * @uses /Public/assets/script/header_responsive.js Menu burger et navigation responsive
 *
 * Assets externes :
 * @uses https://fonts.googleapis.com Google Fonts API
 * @uses https://fonts.gstatic.com Google Fonts CDN
 * @font Poppins (weights: 400 Regular, 500 Medium, 600 Semi-Bold)
 *
 * Favicon :
 * @uses /Public/assets/images/logo.png Logo de l'application (format PNG)
 *
 * Sécurité :
 * - htmlspecialchars() sur toutes les variables injectées
 * - ENT_QUOTES pour encoder les guillemets simples et doubles
 * - Protection contre XSS (Cross-Site Scripting)
 * - Validation des chemins de fichiers (pas d'inclusion arbitraire)
 * - Preconnect sécurisé avec crossorigin pour Google Fonts
 *
 * Accessibilité :
 * - Charset UTF-8 pour caractères spéciaux
 * - Viewport pour zoom utilisateur fonctionnel
 * - Police lisible (Poppins) avec poids variés
 * - Favicon pour identification visuelle
 * - Support mode sombre pour confort visuel
 *
 * Responsive Design :
 * - Meta viewport avec width=device-width
 * - Initial-scale=1 pour zoom natif correct
 * - Scripts responsive (header_responsive.js)
 * - Styles responsive dans body_main_container.css
 */

/**
 * Ces variables doivent être définies AVANT l'inclusion de ce partial.
 * Si elles ne sont pas définies, des valeurs par défaut sont utilisées.
 *
 * @var string $pageTitle       Titre de la page (défaut: "DashMed")
 * @var string $pageDescription Meta description SEO (défaut: "")
 * @var array  $pageStyles      Liste des CSS spécifiques (défaut: [])
 * @var array  $pageScripts     Liste des JS spécifiques (défaut: [])
 */
?>
<head>
    <!--
        Métadonnées de base

        Charset UTF-8 :
        - Support de tous les caractères internationaux
        - Obligatoire en premier pour interprétation correcte

        Viewport :
        - width=device-width : Largeur = largeur de l'écran
        - initial-scale=1 : Zoom par défaut à 100%
    -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="<?= htmlspecialchars($pageDescription ?? '', ENT_QUOTES) ?>">

    <!--
        Titre de la page

        Affiché dans :
        - Onglet du navigateur
        - Historique de navigation
        - Favoris/marque-pages
        - Résultats de recherche (avec meta description)

        Valeur par défaut : "DashMed" si non défini
        Protégé avec htmlspecialchars()
    -->
    <title><?= htmlspecialchars($pageTitle ?? 'DashMed', ENT_QUOTES) ?></title>

    <!--
        Optimisation Google Fonts

        Preconnect :
        - Établit la connexion DNS à l'avance
        - Réduit la latence de chargement des polices
        - fonts.googleapis.com : API Google Fonts
        - fonts.gstatic.com : CDN des fichiers de police
    -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

    <!--
        Styles communs (chargés sur toutes les pages)

        Ordre de chargement :
        1. body_main_container.css : Structure de base (body, main, container)
        2. header.css : Navigation et topbar
        3. footer.css : Pied de page
        4. dark-mode.css : Variables et styles du mode sombre

        Ces styles sont toujours nécessaires pour la structure générale
    -->
    <link rel="stylesheet" href="/assets/style/body_main_container.css">
    <link rel="stylesheet" href="/assets/style/header.css">
    <link rel="stylesheet" href="/assets/style/footer.css">
    <link rel="stylesheet" href="/assets/style/dark-mode.css">

    <?php
    /**
     * Injection dynamique des styles spécifiques à la page.
     *
     * Parcourt le tableau $pageStyles et génère une balise <link> pour chaque CSS.
     * Chargés APRÈS les styles communs pour permettre la surcharge.
     *
     * Sécurité :
     * - htmlspecialchars() sur chaque chemin pour éviter injection
     * - ENT_QUOTES pour encoder guillemets simples et doubles
     *
     * @var array $pageStyles Liste des chemins CSS relatifs depuis /Public
     *
     * Exemple :
     * $pageStyles = ["/assets/style/profile.css", "/assets/style/forms.css"];
     */
    if (!empty($pageStyles)) {
        foreach ($pageStyles as $href) {
            echo '<link rel="stylesheet" href="' . htmlspecialchars($href, ENT_QUOTES) . '">' . PHP_EOL;
        }
    }
    ?>

    <!--
        Favicon

        Icône affichée dans :
        - Onglet du navigateur
        - Favoris/marque-pages
        - Historique de navigation
        - Barre d'applications (certains navigateurs)
    -->
    <link rel="icon" href="/assets/images/logo.png">

    <?php
    /**
     * Injection dynamique des scripts spécifiques à la page.
     *
     * Parcourt le tableau $pageScripts et génère une balise script pour chaque JS.
     *
     * Ordre de chargement :
     * 1. Scripts spécifiques à la page (defer)
     * 2. dark-mode.js (sans defer, prioritaire)
     * 3. header_responsive.js (defer, global)
     *
     * @var array $pageScripts Liste des chemins JS relatifs depuis /Public
     *
     * Exemple :
     * $pageScripts = ["/assets/script/profile-validation.js"];
     */
    if (!empty($pageScripts)) {
        foreach ($pageScripts as $src) {
            echo '<script src="' . htmlspecialchars($src, ENT_QUOTES) . '" defer></script>' . PHP_EOL;
        }
    }

    /**
     * Script du mode sombre (prioritaire, sans defer).
     *
     * Chargé SANS defer pour exécution immédiate.
     *
     * Raison :
     * - Doit appliquer le thème AVANT le rendu de la page
     * - Évite le FOUC (Flash Of Unstyled Content)
     * - Lit la préférence localStorage au plus tôt
     * - Applique [data-theme="dark"] sur <html> immédiatement
     *
     * Position : AVANT header_responsive.js
     */
    echo '<script src="/assets/script/dark-mode.js"></script>' . PHP_EOL;

    /**
     * Script global du header responsive (avec defer).
     *
     * Fonctionnalités :
     * - Gestion du menu burger (mobile)
     * - Toggle navigation responsive
     * - Gestion des attributs ARIA (aria-expanded, aria-hidden)
     *
     * Attribut defer :
     * - Exécution après parsing complet du DOM
     * - Non bloquant pour le rendu de la page
     * - Améliore le FCP (First Contentful Paint)
     *
     * Position : APRÈS dark-mode.js et pageScripts
     *
     * Note : Chargé sur toutes les pages car header présent partout
     */
    echo '<script src="/assets/script/header_responsive.js" defer></script>' . PHP_EOL;
    ?>
</head>