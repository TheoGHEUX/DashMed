<?php

/**
 * Partial : Bouton Sélecteur Patient
 *
 * Le bouton qui déclenche l'ouverture de la liste des patients.
 *
 * @package Views/Dashboard/Partials
 */

?>
<div class="dashboard-actions">
    <button class="btn-patients" id="togglePatients"
            aria-expanded="false"
            aria-controls="patientsList">
        <span class="btn-patients-label">Sélectionner un patient</span>
        <span class="btn-patients-arrow" aria-hidden="true">▾</span>
    </button>
</div>