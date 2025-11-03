<?php
/**
 * Variables pour le template de la page
 */
$pageTitle = "Dashboard";
$pageDescription = "Tableau de bord - Suivi médical";
$pageStyles = [
    '/assets/style/dashboard.css'
];
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
    <h1 class="page-title">Suivi médical</h1>

    <section class="dashboard-grid" aria-label="Statistiques de santé">
        <article class="card chart-card">
            <button class="card-expand" aria-label="Agrandir">⤢</button>
            <h2 class="card-title">Tendance de la tension</h2>
            <canvas id="chart-blood-pressure" width="600" height="200" aria-label="Graphique tension"></canvas>
            <div class="card-footer">
                <div class="large-value" id="value-bp">-</div>
                <div class="small-note" id="note-bp">Moyenne dernière semaine</div>
            </div>
        </article>

        <article class="card chart-card">
            <button class="card-expand" aria-label="Agrandir">⤢</button>
            <h2 class="card-title">Fréquence cardiaque</h2>
            <canvas id="chart-heart-rate" width="600" height="200" aria-label="Graphique pouls"></canvas>
            <div class="card-footer">
                <div class="large-value" id="value-hr">-</div>
                <div class="small-note" id="note-hr">BPM, dernière mesure</div>
            </div>
        </article>

        <article class="card chart-card">
            <button class="card-expand" aria-label="Agrandir">⤢</button>
            <h2 class="card-title">Respiration</h2>
            <canvas id="chart-respiration" width="600" height="200" aria-label="Graphique respiration"></canvas>
            <div class="card-footer">
                <div class="large-value" id="value-resp">-</div>
                <div class="small-note" id="note-resp">Resp/min</div>
            </div>
        </article>

        <article class="card chart-card">
            <button class="card-expand" aria-label="Agrandir">⤢</button>
            <h2 class="card-title">Température</h2>
            <canvas id="chart-temperature" width="600" height="200" aria-label="Graphique température"></canvas>
            <div class="card-footer">
                <div class="large-value" id="value-temp">-</div>
                <div class="small-note" id="note-temp">°C, dernière mesure</div>
            </div>
        </article>

        <article class="card chart-card">
            <button class="card-expand" aria-label="Agrandir">⤢</button>
            <h2 class="card-title">Glycémie (tendance)</h2>
            <canvas id="chart-glucose-trend" width="600" height="200" aria-label="Graphique glycémie"></canvas>
            <div class="card-footer">
                <div class="large-value" id="value-glucose-trend">-</div>
                <div class="small-note" id="note-glucose">mmol/L</div>
            </div>
        </article>

        <article class="card chart-card">
            <button class="card-expand" aria-label="Agrandir">⤢</button>
            <h2 class="card-title">Activité (pas)</h2>
            <canvas id="chart-activity" width="600" height="200" aria-label="Graphique activité"></canvas>
            <div class="card-footer">
                <div class="large-value" id="value-activity">-</div>
                <div class="small-note" id="note-activity">Aujourd'hui</div>
            </div>
        </article>
    </section>

    <section class="dashboard-legend">
        <p>Les valeurs affichées sont des placeholders.</p>
    </section>
</main>

<?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>