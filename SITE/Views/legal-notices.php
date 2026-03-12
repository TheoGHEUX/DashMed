<?php
/**
 * Vue : Page des mentions légales (Legal Notices)
 * 
 * Cette page présente l'ensemble des mentions légales de l'application DashMed.
 * Elle constitue un point d'information essentiel pour les utilisateurs concernant
 * la politique de confidentialité, les conditions d'utilisation et les droits des
 * utilisateurs en matière de données personnelles et de santé.
 * 
 * Fonctionnalités :
 * - Affichage structuré des mentions légales en panneaux distincts
 * - Politique de confidentialité (RGPD, hébergement sécurisé)
 * - Conditions d'utilisation professionnelle de la plateforme
 * - Droits des utilisateurs et gestion des données de santé
 * - Mise en page responsive avec système de grille adaptative
 * - Liens "En savoir plus" vers sections détaillées
 * - Support du mode sombre avec contraste optimisé
 * 
 * Sections de la page :
 * 1. En-tête :
 *    - Titre principal "Mentions légales"
 *    - Date de dernière mise à jour
 * 
 * 2. Politique de confidentialité :
 *    - Protection des données selon RGPD
 *    - Finalités et durées de conservation
 *    - Hébergement sécurisé
 * 
 * 3. Conditions d'utilisation :
 *    - Usage professionnel pour établissements de santé
 *    - Habilitations et gestion des accès
 *    - Sécurité (chiffrement, audit, hébergement conforme)
 *    - SLA et disponibilité de la plateforme
 *    - Répartition des responsabilités
 * 
 * 4. Droits des utilisateurs et gestion des données :
 *    - Droits RGPD (accès, rectification, effacement)
 *    - Logs et traçabilité des actions
 *    - Procédures de demande via DPO
 *    - Obligations légales de conservation
 * 
 * Structure visuelle :
 * - Header public (navigation pour visiteurs)
 * - Container principal avec grille de panneaux
 * - 3 panneaux informatifs avec mise en page adaptive
 * - Footer commun avec liens complémentaires
 * 
 * Public cible :
 * - Tous les utilisateurs de la plateforme (authentifiés ou non)
 * - Établissements de santé partenaires
 * - Professionnels de santé utilisant DashMed
 * - Patients et utilisateurs finaux
 * - Responsables de la conformité et DPO
 * 
 * Parcours utilisateur :
 * - Accès depuis le footer ou lien direct
 * - Lecture des mentions légales par section
 * - Liens "En savoir plus" pour détails complets
 * - Contact DPO pour demandes spécifiques
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
 * @see        \SITE\Controllers\LegalController.php Contrôleur gérant cette vue
 * @see        \SITE\Views\partials\footer.php Footer contenant le lien vers cette page
 * @see        \SITE\Views\partials\headerPublic.php Header pour visiteurs
 * 
 * @requires   PHP >= 7.4
 * 
 * Dépendances CSS :
 * @uses /Public/assets/style/legal_notices.css Styles de la page (grille, panneaux, thème sombre)
 * 
 * Dépendances JavaScript :
 * Aucune dépendance JavaScript spécifique pour cette page
 * 
 * Variables de template :
 * @var string $pageTitle       Titre de la page, par défaut "Mentions légales"
 * @var string $pageDescription Meta description SEO pour les mentions légales
 * @var array  $pageStyles      Chemins des CSS : ["/assets/style/legal_notices.css"]
 * @var array  $pageScripts     Chemins des scripts JS (vide pour cette page)
 * 
 * Conformité légale :
 * - Conforme RGPD (Règlement Général sur la Protection des Données)
 * - Respect des obligations de transparence
 * - Informations sur l'hébergement des données de santé
 * - Procédures d'exercice des droits des utilisateurs
 * - Traçabilité et journalisation des accès
 * - Conservation réglementaire des dossiers médicaux
 * 
 * SEO & Marketing :
 * - Mots-clés : mentions légales, RGPD, données de santé, confidentialité
 * - Page indexable pour transparence et conformité
 * - Mise à jour régulière indiquée (dernière : 22 octobre 2025)
 * 
 * Accessibilité :
 * - Structure sémantique (section, h1, h3, ul, li)
 * - Hiérarchie des titres respectée
 * - Contraste texte/fond optimisé (force dark text on light background)
 * - Support mode sombre avec ajustements spécifiques
 * - Liens identifiables et focus visible
 * - Navigation au clavier fonctionnelle
 * 
 * Architecture CSS :
 * - Système de grille responsive (2 colonnes desktop, 1 colonne mobile)
 * - Panneaux avec classes .panel, .short, .long, .full
 * - Propriétés CSS custom pour cohérence du thème
 * - Transitions fluides entre modes clair/sombre
 * - Override forcé des couleurs en mode sombre (!important)
 */
?>
<!doctype html>
<html lang="fr">
<?php include __DIR__ . '/partials/head.php'; ?>
<body>
<?php include __DIR__ . '/partials/headerPublic.php'; ?>

<main class="content">
    <div class="container">
        <h1>Mentions légales</h1>
        <p class="muted">Dernière mise à jour: 22 octobre 2025</p>

        <section class="legal-grid">
            <div class="panel short">
                <h3>Politique de confidentialité</h3>
                <p>Nous protégeons les données personnelles et de santé selon les normes en vigueur (RGPD, hébergement sécurisé). Les finalités et durées de conservation sont précisées dans notre politique complète.</p>
                <a class="more" href="#privacy-details">En savoir plus</a>
            </div>

            <div class="panel">
                <h3>Conditions d’utilisation</h3>
                <p>Ces conditions définissent l'usage professionnel de la plateforme MedDash par les établissements de santé. Elles couvrent les obligations de l'établissement utilisatrice, les engagements de MedDash en matière de sécurité et les limites de responsabilité.</p>
                <ul>
                    <li><strong>Habilitations</strong> — gestion des accès par l'établissement ; identifiants personnels requis.</li>
                    <li><strong>Sécurité</strong> — chiffrement, journaux d'audit et hébergement sécurisé conformes aux obligations applicables aux données de santé.</li>
                    <li><strong>SLA</strong> — disponibilités et procédures d'incident précisées contractuellement ; maintenance planifiée annoncée à l'avance.</li>
                    <li><strong>Responsabilités</strong> — l'établissement reste responsable du contenu clinique et des décisions médicales ; MedDash assure la plateforme et son intégrité technique.</li>
                </ul>
                <a class="more" href="#terms-details">Lire les détails</a>
            </div>

            <div class="panel full long">
                <h3>Droits des utilisateurs et gestion des données</h3>
                <p>Les utilisateurs et les patients bénéficient de droits encadrés par le RGPD et la réglementation relative aux données de santé. Les demandes d'accès, de rectification ou d'effacement sont traitées selon des procédures définies en collaboration avec l'établissement.</p>
                <ul>
                    <li><strong>Droit d'accès :</strong> possibilité de demander une copie des données détenues vous concernant.</li>
                    <li><strong>Droit de rectification :</strong> correction des données inexactes via les procédures internes de l'établissement.</li>
                    <li><strong>Droit à l'effacement :</strong> supprimable sous réserve des obligations légales de conservation (dossiers médicaux, archives réglementaires).</li>
                    <li><strong>Logs et traçabilité :</strong> toutes les consultations et actions sont journalisées pour garantir la traçabilité et la sécurité.</li>
                    <li><strong>Procédure de demande :</strong> les demandes doivent être adressées au DPO ou contact indiqué par l'établissement ; MedDash assiste techniquement le traitement de ces demandes.</li>
                </ul>
                <a class="more" href="#rights-details">Procédure complète</a>
            </div>
        </section>
    </div>
</main>

<?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>
