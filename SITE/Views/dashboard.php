<?php
/**
 * Vue : Tableau de bord médical (Dashboard)
 *
 * Cette page constitue le cœur de l'application DashMed. Elle affiche une grille
 * interactive de graphiques permettant de visualiser l'évolution des constantes
 * vitales et indicateurs de santé du patient.
 *
 * Fonctionnalités :
 * - Affichage de 7 graphiques de suivi médical (tension, pouls, respiration, etc.)
 * - Mode édition permettant de réorganiser/masquer les graphiques
 * - Panneau de gestion des graphiques (ajout/suppression par glisser-déposer)
 * - Redimensionnement dynamique des cartes en mode édition
 * - Affichage des dernières valeurs mesurées pour chaque indicateur
 * - Graphiques interactifs générés avec Chart.js
 *
 * Graphiques disponibles :
 * - Tension artérielle (mmHg) - Systolique/Diastolique
 * - Fréquence cardiaque (BPM) - Battements par minute
 * - Fréquence respiratoire (resp/min) - Cycles respiratoires
 * - Température corporelle (°C) - Température centrale
 * - Glycémie (mmol/L) - Taux de glucose sanguin
 * - Poids (kg) - Évolution pondérale
 * - Saturation en oxygène (%) - SpO2
 *
 * Interactivité :
 * - Bouton "Modifier" : active/désactive le mode édition
 * - Mode édition : permet le drag & drop des cartes
 * - Poignées de redimensionnement visibles en mode édition
 * - Zone de dépôt pour supprimer des graphiques
 *
 * Architecture technique :
 * - Grille responsive CSS Grid Layout
 * - Canvas HTML5 pour les graphiques (Chart.js)
 * - JavaScript vanilla pour les interactions
 * - ARIA labels pour l'accessibilité
 *
 * @package    DashMed
 * @subpackage Views
 * @category   Frontend
 * @version    1.2.0
 * @since      1.0.0
 * @author     FABRE Alexis
 * @author     GHEUX Théo
 * @author     JACOB Alexandre
 * @author     TAHA CHAOUI Amir
 * @author     UYSUN Ali
 *
 * @see        \SITE\Views\accueil.php Page d'accueil redirigeant vers ce dashboard
 * @see        \SITE\Views\partials\headerPrivate.php Header pour utilisateurs authentifiés
 *
 * @requires   PHP >= 7.4
 * @requires   Session active avec $_SESSION['user']
 * @requires   Chart.js (bibliothèque JavaScript pour les graphiques)
 *
 * @global array $_SESSION Données de session pour l'authentification
 *
 * Dépendances CSS :
 * @uses /Public/assets/style/dashboard.css Styles du tableau de bord (grille, cartes, graphiques)
 *
 * Dépendances JavaScript :
 * @uses /Public/assets/script/dashboard_charts.js Gestion des graphiques et interactions
 *
 * Structure des données :
 * - Les données des graphiques sont chargées via AJAX depuis l'API backend
 * - Format attendu : JSON avec timestamps et valeurs mesurées
 * - Les placeholders sont affichés en attendant les données réelles
 *
 * Variables de template :
 * @var string $pageTitle       Titre de la page (affiché dans <title>)
 * @var string $pageDescription Meta description pour le SEO
 * @var array  $pageStyles      Chemins des feuilles de style à inclure
 * @var array  $pageScripts     Chemins des scripts JavaScript à inclure
 *
 * Accessibilité :
 * - Utilisation de balises sémantiques (article, section, h1-h2)
 * - ARIA labels sur les graphiques pour les lecteurs d'écran
 * - Navigation au clavier supportée
 * - Contraste des couleurs conforme WCAG 2.1
 */

// ============================================================================
// CONFIGURATION : Variables du template
// ============================================================================

/**
 * Titre de la page affiché dans la balise <title> et l'onglet du navigateur.
 *
 * @var string $pageTitle
 */
$pageTitle = "Dashboard";

/**
 * Description de la page pour les moteurs de recherche (SEO).
 * Décrit le contenu du tableau de bord médical.
 *
 * @var string $pageDescription
 */
$pageDescription = "Tableau de bord - Suivi médical";

/**
 * Liste des feuilles de style CSS spécifiques à cette page.
 * Contient les styles pour la grille de graphiques, les cartes et les interactions.
 *
 * @var array<int, string> $pageStyles Chemins relatifs depuis /Public
 */
$pageStyles = [
        '/assets/style/dashboard.css'
];

/**
 * Liste des scripts JavaScript spécifiques à cette page.
 * Gère la création des graphiques Chart.js, le mode édition et le drag & drop.
 *
 * @var array<int, string> $pageScripts Chemins relatifs depuis /Public
 */
$pageScripts = [
        '/assets/script/dashboard_charts.js'
];
?>
<!DOCTYPE html>
<html lang="fr">
<?php include __DIR__ . '/partials/head.php'; ?>

<body>
<?php include __DIR__ . '/partials/headerPrivate.php'; ?>

<main class="dashboard-main container">
    <div class="dashboard-header">
        <h1 class="page-title">Suivi médical</h1>
        <button class="btn-edit-mode" id="toggleEditMode">
            <span class="icon-edit">✎</span>
            <span class="text-edit">Modifier</span>
        </button>
    </div>

    <section class="dashboard-grid" id="dashboardGrid" aria-label="Statistiques de santé">
        <article class="card chart-card" data-chart-id="blood-pressure">
            <div class="resize-handle" style="display: none;"></div>
            <h2 class="card-title">Tendance de la tension (mmHg)</h2>
            <canvas id="chart-blood-pressure" width="600" height="200" aria-label="Graphique tension"></canvas>
            <div class="card-footer">
                <div class="large-value" id="value-bp">-</div>
                <div class="small-note" id="note-bp">mmHg, dernière mesure</div>
            </div>
        </article>

        <article class="card chart-card" data-chart-id="heart-rate">
            <div class="resize-handle" style="display: none;"></div>
            <h2 class="card-title">Fréquence cardiaque (BPM)</h2>
            <canvas id="chart-heart-rate" width="600" height="200" aria-label="Graphique pouls"></canvas>
            <div class="card-footer">
                <div class="large-value" id="value-hr">-</div>
                <div class="small-note" id="note-hr">BPM, dernière mesure</div>
            </div>
        </article>

        <article class="card chart-card" data-chart-id="respiration">
            <div class="resize-handle" style="display: none;"></div>
            <h2 class="card-title">Fréquence respiratoire</h2>
            <canvas id="chart-respiration" width="600" height="200" aria-label="Graphique respiration"></canvas>
            <div class="card-footer">
                <div class="large-value" id="value-resp">-</div>
                <div class="small-note" id="note-resp">Resp/min</div>
            </div>
        </article>

        <article class="card chart-card" data-chart-id="temperature">
            <div class="resize-handle" style="display: none;"></div>
            <h2 class="card-title">Température corporelle (°C)</h2>
            <canvas id="chart-temperature" width="600" height="200" aria-label="Graphique température"></canvas>
            <div class="card-footer">
                <div class="large-value" id="value-temp">-</div>
                <div class="small-note" id="note-temp">°C, dernière mesure</div>
            </div>
        </article>

        <article class="card chart-card" data-chart-id="glucose-trend">
            <div class="resize-handle" style="display: none;"></div>
            <h2 class="card-title">Tendance glycémique (mmol/L)</h2>
            <canvas id="chart-glucose-trend" width="600" height="200" aria-label="Graphique glycémie"></canvas>
            <div class="card-footer">
                <div class="large-value" id="value-glucose-trend">-</div>
                <div class="small-note" id="note-glucose">mmol/L</div>
            </div>
        </article>

        <article class="card chart-card" data-chart-id="weight">
            <div class="resize-handle" style="display: none;"></div>
            <h2 class="card-title">Évolution du poids (kg)</h2>
            <canvas id="chart-weight" width="600" height="200" aria-label="Graphique poids"></canvas>
            <div class="card-footer">
                <div class="large-value" id="value-weight">-</div>
                <div class="small-note" id="note-weight">kg, dernière mesure</div>
            </div>
        </article>

        <article class="card chart-card" data-chart-id="oxygen-saturation">
            <div class="resize-handle" style="display: none;"></div>
            <h2 class="card-title">Saturation en oxygène (%)</h2>
            <canvas id="chart-oxygen-saturation" width="600" height="200" aria-label="Graphique saturation oxygène"></canvas>
            <div class="card-footer">
                <div class="large-value" id="value-oxygen">-</div>
                <div class="small-note" id="note-oxygen">%, dernière mesure</div>
            </div>
        </article>
    </section>

    <div class="add-chart-panel" id="addChartPanel" style="display: none;">
        <h3>Glissez un graphique ici pour le supprimer, ou glissez-le sur la grille pour l'ajouter</h3>
        <div class="available-charts" id="availableCharts"></div>
    </div>

    <section class="dashboard-legend">
        <p>Les valeurs affichées sont des placeholders.</p>
    </section>
</main>

<?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>