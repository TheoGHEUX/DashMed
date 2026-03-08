<?php
/**
 * Partial : État Vide
 *
 * Affiché lorsqu'aucun patient n'est assigné au médecin.
 *
 * @package Views/Dashboard/Partials
 */
?>
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