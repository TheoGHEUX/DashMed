<?php
/**
 * Partial : Footer commun (Pied de page de l'application)
 *
 * Ce composant constitue le pied de page partagé de l'application DashMed, utilisé
 * sur toutes les pages publiques et privées. Il affiche les informations de copyright
 * avec l'année dynamique, des liens vers les pages légales et informatives, et maintient
 * une cohérence visuelle sur l'ensemble du site. Le footer s'adapte automatiquement
 * au mode clair/sombre et est entièrement responsive.
 *
 * Fonctionnalités :
 * - Affichage du copyright avec année dynamique (date("Y"))
 * - Nom de marque "DashMed" avec mention légale
 * - Liens vers pages légales et informatives (optionnels, selon implémentation)
 * - Design minimaliste et épuré
 * - Responsive et adapté à tous les écrans
 * - Support du mode sombre avec transition fluide
 * - Structure sémantique HTML5
 *
 * Éléments du footer :
 * Container centré :
 *    - Copyright dynamique (© + année courante)
 *    - Nom de marque "DashMed"
 *    - Mention "Tous droits réservés"
 *
 * Structure visuelle :
 * - Barre horizontale (footer) en bas de page
 * - Container centré avec largeur maximale
 * - Texte centré (ou aligné selon design)
 * - Fond contrasté avec le contenu principal
 * - Espacement vertical confortable
 *
 * @package    DashMed
 * @subpackage Views\Partials
 * @category   Frontend
 * @version    1.0.0
 * @since      1.0.0
 * @author     FABRE Alexis
 * @author     GHEUX Théo
 * @author     JACOB Alexandre
 * @author     TAHA CHAOUI Amir
 * @author     UYSUN Ali
 *
 * @see        \SITE\Views\index.php Page d'accueil utilisant ce footer
 * @see        \SITE\Views\legal-notices.php Page des mentions légales
 * @see        \SITE\Views\map.php Page du plan du site
 * @see        \SITE\Views\partials\headerPublic.php Header public
 * @see        \SITE\Views\partials\headerPrivate.php Header privé
 *
 * @requires   PHP >= 7.4
 *
 * Dépendances CSS :
 * @uses /Public/assets/style/footer.css Styles du footer (fond, texte, liens)
 * @uses /Public/assets/style/dark-mode.css Adaptation au mode sombre
 *
 * Dépendances JavaScript :
 * Aucune dépendance JavaScript spécifique pour ce composant
 *
 * Variables PHP :
 * Aucune variable externe requise (utilise date() pour l'année)
 *
 * Fonctions PHP utilisées :
 * @uses date() Récupère l'année courante au format YYYY
 *
 * Classes CSS principales :
 * @class footer    Conteneur principal du pied de page
 * @class container Conteneur centré avec largeur max (partagé avec header)
 *
 * Responsive Design :
 * - Desktop : Footer pleine largeur avec container centré
 * - Tablette : Adaptation de l'espacement
 * - Mobile : Texte centré, liens empilés verticalement (si présents)
 * - Largeur flexible (100% viewport)
 */
?>

<footer class="footer">
    <div class="container">
        © <?= date("Y") ?> DashMed. Tous droits réservés
    </div>
</footer>
