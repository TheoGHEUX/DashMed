<?php

/**
 * Partial : Fiche Info Patient
 *
 * Affiche le résumé du patient sélectionné en haut de page.
 *
 * @package Views/Dashboard/Partials
 */

?>
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