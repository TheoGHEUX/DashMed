<?php

/**
 * Page connectée : Tableau de bord médical
 *
 *
 * Nécessite une session utilisateur active.
 *
 * @package Views
 */

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (empty($_SESSION['user'])) {
    header('Location: /login');
    exit;
}

use Core\Database;

$pageTitle       = $pageTitle ?? "Dashboard";
$pageDescription = $pageDescription ?? "Tableau de bord - Suivi médical";
$pageStyles      = $pageStyles ?? ['/assets/style/dashboard.css'];
$pageScripts     = $pageScripts ?? [
        '/assets/script/dashboard_charts.js',
        '/assets/script/dashboard.js',
        '/assets/script/dashboard_notifications.js',
        '/assets/script/dashboard_generate_data.js'
];
include __DIR__ . '/../partials/head.php';
?>

<body>

<?php include __DIR__ . '/../partials/headerPrivate.php'; ?>


<div id="notification-container" class="notification-container"></div>

<script>
    window.patientChartData = <?= json_encode($chartData ?? [], JSON_UNESCAPED_UNICODE) ?>;
    window.activePatient = <?= json_encode($patient ?? [], JSON_UNESCAPED_UNICODE) ?>;
</script>

<?php if (!isset($noPatient) || $noPatient === false) : ?>
<div class="dashboard-actions">
    <button class="btn-patients" id="togglePatients"
            aria-expanded="false"
            aria-controls="patientsList">
        <span class="btn-patients-label">Sélectionner un patient</span>
        <span class="btn-patients-arrow" aria-hidden="true">▾</span>
    </button>
</div>
<?php endif; ?>

<?php if (!isset($noPatient) || $noPatient === false) : ?>
<section class="patient-active">
    <h2>Patient sélectionné</h2>

    <?php if (!empty($patient)) : ?>
        <div class="patient-card">
            <p class="patient-name">
                <?= htmlspecialchars($patient['prenom']) ?> <?= htmlspecialchars($patient['nom']) ?>
            </p>
            <ul class="patient-meta">
                <li><strong>Sexe :</strong> <?= htmlspecialchars($patient['sexe'] ?? '-') ?></li>
                <li><strong>Date de naissance :</strong> <?= htmlspecialchars($patient['date_naissance'] ?? '-') ?></li>
                <li><strong>Groupe sanguin :</strong> <?= htmlspecialchars($patient['groupe_sanguin'] ?? '-') ?></li>
                <li><strong>Téléphone :</strong> <?= htmlspecialchars($patient['telephone'] ?? '-') ?></li>
                <li><strong>Adresse :</strong>
                    <?= htmlspecialchars($patient['adresse'] ?? '-') ?>,
                    <?= htmlspecialchars($patient['code_postal'] ?? '-') ?>
                    <?= htmlspecialchars($patient['ville'] ?? '-') ?>
                </li>
                <li><strong>E-mail :</strong> <?= htmlspecialchars($patient['email'] ?? '-') ?></li>
                <li><strong>ID Dashmed :</strong> <?= htmlspecialchars($patient['pt_id'] ?? '-') ?></li>
            </ul>
        </div>
    <?php else : ?>
        <p>Aucun patient sélectionné.</p>
    <?php endif; ?>
</section>
<?php endif; ?>

<section class="patients-list-overlay" id="patientsList">
    <div class="patients-list-content">
        <h2>Patients suivis</h2>
        <?php if (empty($patients)) : ?>
            <p>Aucun patient associé.</p>
        <?php else : ?>
            <ul>
                <?php foreach ($patients as $p) : ?>
                    <li class="patient-item"
                        data-nom="<?= htmlspecialchars($p['nom']) ?>"
                        data-prenom="<?= htmlspecialchars($p['prenom']) ?>">
                        <a href="/dashboard?patient=<?= urlencode($p['pt_id']) ?>">
                            <?= htmlspecialchars($p['prenom']) ?> <?= htmlspecialchars($p['nom']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</section>

<main class="dashboard-main container">
    <?php if (!isset($noPatient) || $noPatient === false) : ?>
    <div class="dashboard-header">
        <h1 class="page-title"><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></h1>
        <button class="btn-edit-mode" id="toggleEditMode" aria-pressed="false" aria-label="Activer le mode édition">
            <span class="icon-edit" aria-hidden="true">✎</span>
            <span class="text-edit">Modifier</span>
        </button>
    </div>
    <?php endif; ?>

    <?php if (isset($noPatient) && $noPatient === true) : ?>
        <!-- Message quand aucun patient n'est associé au médecin -->
        <section class="dashboard-empty-state" role="status" aria-label="Aucun patient">
            <div class="empty-state-content">
                <div class="empty-state-icon" aria-hidden="true">👥</div>
                <h2 class="empty-state-title">Aucun patient associé</h2>
                <p class="empty-state-description">
                    Vous n'avez actuellement aucun patient associé à votre compte.
                    <br>Les patients vous seront assignés par l'administration.
                </p>
            </div>
        </section>
    <?php else : ?>
    <section class="dashboard-grid" id="dashboardGrid" aria-label="Statistiques de santé">
        <?php if (!empty($patient)) : ?>
        <article class="card chart-card" data-chart-id="blood-pressure">
            <div class="resize-handle" style="display: none;" aria-hidden="true"></div>
            <h2 class="card-title">Tendance de la tension (mmHg)</h2>
            <canvas id="chart-blood-pressure" width="600" height="200" aria-label="Graphique tension"></canvas>
            <div class="card-footer">
                <div class="large-value" id="value-bp">-</div>
                <div class="small-note">dernière mesure</div>
            </div>
        </article>

        <article class="card chart-card" data-chart-id="heart-rate">
            <div class="resize-handle" style="display: none;" aria-hidden="true"></div>
            <h2 class="card-title">Fréquence cardiaque (BPM)</h2>
            <canvas id="chart-heart-rate" width="600" height="200" aria-label="Graphique pouls"></canvas>
            <div class="card-footer">
                <div class="large-value" id="value-hr">-</div>
                <div class="small-note">dernière mesure</div>
            </div>
        </article>

        <article class="card chart-card" data-chart-id="respiration">
            <div class="resize-handle" style="display: none;" aria-hidden="true"></div>
            <h2 class="card-title">Fréquence respiratoire</h2>
            <canvas id="chart-respiration" width="600" height="200" aria-label="Graphique respiration"></canvas>
            <div class="card-footer">
                <div class="large-value" id="value-resp">-</div>
                <div class="small-note">dernière mesure</div>
            </div>
        </article>

        <article class="card chart-card" data-chart-id="temperature">
            <div class="resize-handle" style="display: none;" aria-hidden="true"></div>
            <h2 class="card-title">Température corporelle (°C)</h2>
            <canvas id="chart-temperature" width="600" height="200" aria-label="Graphique température"></canvas>
            <div class="card-footer">
                <div class="large-value" id="value-temp">-</div>
                <div class="small-note">dernière mesure</div>
            </div>
        </article>

        <article class="card chart-card" data-chart-id="glucose-trend">
            <div class="resize-handle" style="display: none;" aria-hidden="true"></div>
            <h2 class="card-title">Tendance glycémique (mmol/L)</h2>
            <canvas id="chart-glucose-trend" width="600" height="200" aria-label="Graphique glycémie"></canvas>
            <div class="card-footer">
                <div class="large-value" id="value-glucose-trend">-</div>
                <div class="small-note">dernière mesure</div>
            </div>
        </article>

        <article class="card chart-card" data-chart-id="weight">
            <div class="resize-handle" style="display: none;" aria-hidden="true"></div>
            <h2 class="card-title">Évolution du poids (kg)</h2>
            <canvas id="chart-weight" width="600" height="200" aria-label="Graphique poids"></canvas>
            <div class="card-footer">
                <div class="large-value" id="value-weight">-</div>
                <div class="small-note">dernière mesure</div>
            </div>
        </article>

        <article class="card chart-card" data-chart-id="oxygen-saturation">
            <div class="resize-handle" style="display: none;" aria-hidden="true"></div>
            <h2 class="card-title">Saturation en oxygène (%)</h2>
            <canvas id="chart-oxygen-saturation" width="600" height="200"
                    aria-label="Graphique saturation oxygène"></canvas>
            <div class="card-footer">
                <div class="large-value" id="value-oxygen">-</div>
                <div class="small-note">dernière mesure</div>
            </div>
        </article>


        <?php endif; ?>
    </section>

    <div class="add-chart-panel" id="addChartPanel" style="display: none;" aria-hidden="true">
        <h3>Glissez un graphique ici pour le supprimer, ou glissez-le sur la grille pour l'ajouter</h3>
        <div class="available-charts" id="availableCharts" aria-hidden="true"></div>
    </div>

    <?php endif; ?>

    <section class="dashboard-note">
        <p><em>Si l'affichage d'un élément vous semble anormal,
                actualisez la page ou videz le cache du navigateur.</em></p>
    </section>
</main>

<!-- Légende des seuils d'alerte -->

<?php if (isset($noPatient) && $noPatient === true) : ?>
<?php else : ?>
<section class="thresholds-legend">
    <h2>Légende des seuils d'alerte</h2>
    <div class="legend-content">
        <p class="legend-intro">
            Les graphiques affichent des lignes de seuils pour vous aider à identifier
            rapidement les valeurs anormales :
        </p>
        <div class="legend-items">
            <div class="legend-item">
                <div class="legend-line preoccupant" aria-hidden="true"></div>
                <div class="legend-text">
                    <strong>Seuil préoccupant</strong>
                    <span>Valeurs nécessitant une surveillance accrue</span>
                </div>
            </div>
            <div class="legend-item">
                <div class="legend-line urgent" aria-hidden="true"></div>
                <div class="legend-text">
                    <strong>Seuil urgent</strong>
                    <span>Valeurs anormales nécessitant une attention rapide</span>
                </div>
            </div>
            <div class="legend-item">
                <div class="legend-line critique" aria-hidden="true"></div>
                <div class="legend-text">
                    <strong>Seuil critique</strong>
                    <span>Valeurs dangereuses nécessitant une intervention immédiate</span>
                </div>
            </div>
            <div class="legend-item">
                <div class="legend-point alert" aria-hidden="true"></div>
                <div class="legend-text">
                    <strong>Mesure en alerte</strong>
                    <span>Point rouge : valeur au-delà d'un seuil (trop haute ou trop basse)</span>
                </div>
            </div>
        </div>
        <p class="legend-note">
            <strong>Note :</strong> Les lignes pointillées avec espacement large (- - -) indiquent des seuils minimaux,
            tandis que les lignes pointillées avec espacement court (— — —) indiquent des seuils maximaux.
        </p>
    </div>
</section>

<?php endif; ?>

<?php include __DIR__ . '/../partials/footer.php'; ?>

<?php if (!empty($patient) && $patient['pt_id'] == 25) : ?>
    <div class="dashboard-actions">
        <button id="generateDataBtn" class="btn-small">
            Générer 5 mesures
        </button>
    </div>
<?php endif; ?>

</body>
</html>
