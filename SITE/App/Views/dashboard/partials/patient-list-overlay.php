<?php

/**
 * Partial : Overlay Liste Patients
 *
 * La liste cachée qui apparaît quand on clique sur "Sélectionner un patient".
 *
 * @package Views/Dashboard/Partials
 */

?>
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