<?php
/**
 * Vue : Tableau de bord médical (Dashboard)
 *
 * Affiche une grille de cartes graphiques (Chart.js) pour le suivi des constantes
 * vitales. Accès réservé aux utilisateurs authentifiés.
 *
 * @package    DashMed
 * @subpackage Views
 * @category   Frontend
 * @version    1.2
 * @since      1.0
 *
 * Variables attendues :
 * @var string $pageTitle               Titre de la page (défaut : "Dashboard")
 * @var string $pageDescription         Meta description (optionnel)
 * @var array<int,string> $pageStyles   Styles spécifiques (ex: ["/assets/style/dashboard.css"])
 * @var array<int,string> $pageScripts  Scripts spécifiques (ex: ["/assets/script/dashboard_charts.js"])
 */

if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (empty($_SESSION['user'])) {
    header('Location: /login');
    exit;
}

$pageTitle       = $pageTitle ?? "Dashboard";
$pageDescription = $pageDescription ?? "Tableau de bord - Suivi médical";
$pageStyles      = $pageStyles ?? ['/assets/style/dashboard.css'];
$pageScripts     = $pageScripts ?? ['/assets/script/dashboard_charts.js'];

include __DIR__ . '/partials/head.php';
?>
<body>
<?php include __DIR__ . '/partials/headerPrivate.php'; ?>

<main class="dashboard-main container">
    <div class="dashboard-header">
        <h1 class="page-title"><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></h1>
        <button class="btn-edit-mode" id="toggleEditMode" aria-pressed="false" aria-label="Activer le mode édition">
            <span class="icon-edit" aria-hidden="true">✎</span>
            <span class="text-edit">Modifier</span>
        </button>
    </div>

    <section class="dashboard-grid" id="dashboardGrid" aria-label="Statistiques de santé">
        <article class="card chart-card" data-chart-id="blood-pressure">
            <div class="resize-handle" style="display: none;" aria-hidden="true"></div>
            <h2 class="card-title">Tendance de la tension (mmHg)</h2>
            <canvas id="chart-blood-pressure" width="600" height="200" aria-label="Graphique tension"></canvas>
            <div class="card-footer">
                <div class="large-value" id="value-bp">-</div>
                <div class="small-note" id="note-bp">mmHg, dernière mesure</div>
            </div>
        </article>

        <article class="card chart-card" data-chart-id="heart-rate">
            <div class="resize-handle" style="display: none;" aria-hidden="true"></div>
            <h2 class="card-title">Fréquence cardiaque (BPM)</h2>
            <canvas id="chart-heart-rate" width="600" height="200" aria-label="Graphique pouls"></canvas>
            <div class="card-footer">
                <div class="large-value" id="value-hr">-</div>
                <div class="small-note" id="note-hr">BPM, dernière mesure</div>
            </div>
        </article>

        <article class="card chart-card" data-chart-id="respiration">
            <div class="resize-handle" style="display: none;" aria-hidden="true"></div>
            <h2 class="card-title">Fréquence respiratoire</h2>
            <canvas id="chart-respiration" width="600" height="200" aria-label="Graphique respiration"></canvas>
            <div class="card-footer">
                <div class="large-value" id="value-resp">-</div>
                <div class="small-note" id="note-resp">Resp/min</div>
            </div>
        </article>

        <article class="card chart-card" data-chart-id="temperature">
            <div class="resize-handle" style="display: none;" aria-hidden="true"></div>
            <h2 class="card-title">Température corporelle (°C)</h2>
            <canvas id="chart-temperature" width="600" height="200" aria-label="Graphique température"></canvas>
            <div class="card-footer">
                <div class="large-value" id="value-temp">-</div>
                <div class="small-note" id="note-temp">°C, dernière mesure</div>
            </div>
        </article>

        <article class="card chart-card" data-chart-id="glucose-trend">
            <div class="resize-handle" style="display: none;" aria-hidden="true"></div>
            <h2 class="card-title">Tendance glycémique (mmol/L)</h2>
            <canvas id="chart-glucose-trend" width="600" height="200" aria-label="Graphique glycémie"></canvas>
            <div class="card-footer">
                <div class="large-value" id="value-glucose-trend">-</div>
                <div class="small-note" id="note-glucose">mmol/L</div>
            </div>
        </article>

        <article class="card chart-card" data-chart-id="weight">
            <div class="resize-handle" style="display: none;" aria-hidden="true"></div>
            <h2 class="card-title">Évolution du poids (kg)</h2>
            <canvas id="chart-weight" width="600" height="200" aria-label="Graphique poids"></canvas>
            <div class="card-footer">
                <div class="large-value" id="value-weight">-</div>
                <div class="small-note" id="note-weight">kg, dernière mesure</div>
            </div>
        </article>

        <article class="card chart-card" data-chart-id="oxygen-saturation">
            <div class="resize-handle" style="display: none;" aria-hidden="true"></div>
            <h2 class="card-title">Saturation en oxygène (%)</h2>
            <canvas id="chart-oxygen-saturation" width="600" height="200" aria-label="Graphique saturation oxygène"></canvas>
            <div class="card-footer">
                <div class="large-value" id="value-oxygen">-</div>
                <div class="small-note" id="note-oxygen">%, dernière mesure</div>
            </div>
        </article>
    </section>

    <div class="add-chart-panel" id="addChartPanel" style="display: none;" aria-hidden="true">
        <h3>Glissez un graphique ici pour le supprimer, ou glissez-le sur la grille pour l'ajouter</h3>
        <div class="available-charts" id="availableCharts" aria-hidden="true"></div>
    </div>

    <section class="dashboard-legend">
        <p>Les valeurs affichées sont des placeholders.</p>
    </section>
</main>

<?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>