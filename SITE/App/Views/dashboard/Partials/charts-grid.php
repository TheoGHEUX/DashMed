<?php
/**
 * Partial : Grille des Graphiques
 *
 * Contient tous les conteneurs <canvas> pour Chart.js.
 *
 * @package Views/Dashboard/Partials
 */
?>
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
            <canvas id="chart-oxygen-saturation" width="600" height="200" aria-label="Graphique saturation oxygène"></canvas>
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