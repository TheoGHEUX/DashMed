<?php

/**
 * Partial : Légende
 *
 * Explique les codes couleurs et les seuils utilisés dans les graphiques.
 *
 * @package Views/Dashboard/Partials
 */

?>
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