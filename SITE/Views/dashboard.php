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
 * @version    1.3
 * @since      1.0
 */

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (empty($_SESSION['user'])) {
    header('Location: /login');
    exit;
}

use Core\Database;

$patients = [];
$patient   = null; // Patient acdtif

try {
    $pdo = Database::getConnection();

    // Récupération de tous les patients suivis par le médecin
    $stmt = $pdo->prepare("
        SELECT 
            p.pt_id,
            p.nom,
            p.prenom,
            p.date_naissance,
            p.sexe,
            p.groupe_sanguin,
            p.telephone,
            p.ville,
            p.code_postal,
            p.adresse,
            p.email
        FROM suivre s
        JOIN patient p ON p.pt_id = s.pt_id
        WHERE s.med_id = :med_id
        ORDER BY p.nom, p.prenom
    ");
    $stmt->execute([':med_id' => $_SESSION['user']['id']]);
    $patients = $stmt->fetchAll();

    // Déterminer le patient sélectionné :
    // 1) depuis l'URL ?patient=ID
    // 2) sinon depuis la session (dernier patient consulté)
    // 3) sinon le premier de la liste
    $selectedPtId = $_GET['patient'] ?? $_SESSION['lastPatientId'] ?? ($patients[0]['pt_id'] ?? null);

    if ($selectedPtId) {
        foreach ($patients as $p) {
            if ($p['pt_id'] == $selectedPtId) {
                $patient = $p;
                $_SESSION['lastPatientId'] = $selectedPtId; // Sauvegarde pour la prochaine visite
                break;
            }
        }
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
}

$pageTitle       = $pageTitle ?? "Dashboard";
$pageDescription = $pageDescription ?? "Tableau de bord - Suivi médical";
$pageStyles      = $pageStyles ?? ['/assets/style/dashboard.css'];

// ⚡ Chargement des scripts : chart + dashboard.js
$pageScripts     = $pageScripts ?? [
        '/assets/script/dashboard_charts.js',
        '/assets/script/dashboard.js'
];

include __DIR__ . '/partials/head.php';
?>

<body>

<?php include __DIR__ . '/partials/headerPrivate.php'; ?>

<!-- Données du patient pour les scripts -->
<script>
    window.patientChartData = <?= json_encode($chartData ?? [], JSON_UNESCAPED_UNICODE) ?>;
    window.activePatient = <?= json_encode($patient ?? [], JSON_UNESCAPED_UNICODE) ?>;
</script>

<div class="dashboard-actions">
    <button class="btn-patients" id="togglePatients"
            aria-expanded="false"
            aria-controls="patientsList">
        <span class="btn-patients-label">Sélectionner un patient</span>
        <span class="btn-patients-arrow" aria-hidden="true">▾</span>
    </button>
</div>

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
                <li><strong>Adresse:</strong> <?= htmlspecialchars($patient['adresse'] ?? '-') ?>, <?= htmlspecialchars($patient['code_postal'] ?? '-') ?> <?= htmlspecialchars($patient['ville'] ?? '-') ?></li>
                <li><strong>E-mail :</strong> <?= htmlspecialchars($patient['email'] ?? '-') ?></li>
            </ul>
        </div>
    <?php else : ?>
        <p>Aucun patient sélectionné.</p>
    <?php endif; ?>
</section>

<section class="patients-list-overlay" id="patientsList">
    <div class="patients-list-content">
        <h2>Patients suivis</h2>
        <?php if (empty($patients)) : ?>
            <p>Aucun patient associé.</p>
        <?php else : ?>
            <ul>
                <?php foreach ($patients as $p) : ?>
                    <li class="patient-item"
                        data-pt-id="<?= htmlspecialchars($p['pt_id']) ?>"
                        data-nom="<?= htmlspecialchars($p['nom']) ?>"
                        data-prenom="<?= htmlspecialchars($p['prenom']) ?>"
                        data-date-naissance="<?= htmlspecialchars($p['date_naissance']) ?>"
                        data-sexe="<?= htmlspecialchars($p['sexe']) ?>">
                        <a href="/dashboard?patient=<?= urlencode($p['pt_id']) ?>">
                            <?= htmlspecialchars($p['prenom']) ?> <?= htmlspecialchars($p['nom']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</section>


<?php if (!empty($patient)) : ?>
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
            <canvas id="chart-oxygen-saturation" width="600" height="200"
                    aria-label="Graphique saturation oxygène"></canvas>
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
        <p><br><em>Si l'affichage d'un élément vous semble anormal,
            actualisez la page ou videz le cache du navigateur.</em></p>
    </section>
</main>
<?php endif; ?>

<?php include __DIR__ . '/partials/footer.php'; ?>

</body>
