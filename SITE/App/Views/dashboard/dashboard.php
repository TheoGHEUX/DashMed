<?php
/**
 * Page connectée : Tableau de bord médical (Vue Principale)
 *
 * Ce fichier agit comme un conteneur maître.
 * Il orchestre l'affichage en incluant les différents blocs (Partials)
 * selon le contexte (patient sélectionné ou non).
 *
 * @package Views/Dashboard
 */

// 1. Configuration de la page
// ----------------------------------------------------------------------------
$pageTitle       = $pageTitle ?? "Dashboard";
$pageDescription = $pageDescription ?? "Tableau de bord - Suivi médical";
$pageStyles      = $pageStyles ?? ['/assets/style/dashboard.css'];
$pageScripts     = $pageScripts ?? [
        '/assets/script/dashboard_charts.js',
        '/assets/script/dashboard.js',
        '/assets/script/dashboard_notifications.js',
        '/assets/script/dashboard_predictions.js',
        '/assets/script/dashboard_generate_data.js'
];

// 2. Inclusion du Head Global
// Note : On remonte de Views/Dashboard/ vers Views/partials/
include __DIR__ . '/../partials/head.php';
?>

<body>

<!-- Header Privé -->
<?php include __DIR__ . '/../partials/headerPrivate.php'; ?>

<!-- Conteneur de notifications JS -->
<div id="notification-container" class="notification-container"></div>

<!-- 3. Configuration JS (Variables PHP -> JS) -->
<?php include __DIR__ . '/partials/js-config.php'; ?>

<!-- 4. Sélecteur de Patient (Liste déroulante) -->
<?php if (!isset($noPatient) || $noPatient === false) : ?>
    <?php include __DIR__ . '/partials/patient-selector.php'; ?>
<?php endif; ?>

<!-- 5. Fiche Info du Patient Actif -->
<?php if (!isset($noPatient) || $noPatient === false) : ?>
    <?php include __DIR__ . '/partials/patient-info.php'; ?>
<?php endif; ?>

<!-- 6. Overlay Liste des Patients (Caché par défaut) -->
<?php include __DIR__ . '/partials/patient-list-overlay.php'; ?>

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

    <!-- 7. Logique d'affichage principal -->
    <?php if (isset($noPatient) && $noPatient === true) : ?>

        <!-- Cas A : Aucun patient n'est sélectionné -->
        <?php include __DIR__ . '/partials/empty-state.php'; ?>

    <?php else : ?>

        <!-- Cas B : Affichage des graphiques -->
        <?php include __DIR__ . '/partials/charts-grid.php'; ?>

    <?php endif; ?>

    <section class="dashboard-note">
        <p><em>Si l'affichage d'un élément vous semble anormal, actualisez la page ou videz le cache du navigateur.</em></p>
    </section>
</main>

<!-- 8. Légende des Seuils (Si patient actif) -->
<?php if (!isset($noPatient) || $noPatient === false) : ?>
    <?php include __DIR__ . '/partials/legend.php'; ?>
<?php endif; ?>

<!-- Footer Global -->
<?php include __DIR__ . '/../partials/footer.php'; ?>

<!-- 9. Outils de Débug (Bouton Générer Données) -->
<?php include __DIR__ . '/partials/debug-tools.php'; ?>

</body>
</html>