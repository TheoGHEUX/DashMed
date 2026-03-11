<?php
/**
 * Partial : Outils de Debug
 *
 * Bouton réservé au développement comme le patient de test (ID 25).
 *
 * @package Views/Dashboard/Partials
 */
?>
<?php if (!empty($patient)) : ?>
    <div class="dashboard-actions">
        <button id="generateDataBtn" class="btn-small">
            Générer 20 mesures
        </button>
    </div>
<?php endif; ?>