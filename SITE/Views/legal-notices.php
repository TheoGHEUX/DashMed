<?php
/**
 * Fichier : legal-notices.php
 * Page des mentions légales de l'application DashMed.
 */

$pageTitle = "Mentions légales";
$pageDescription = "Toutes les mentions légales de DashMed";
$pageStyles = ["/assets/style/legal_notices.css"];
$pageScripts = [];
?>
<!doctype html>
<html lang="fr">
<?php include __DIR__ . '/partials/head.php'; ?>
<body>
<?php include __DIR__ . '/partials/headerPublic.php'; ?>

<main class="content">
    <div class="container">
        <h1>Mentions légales</h1>
        <p class="muted">Dernière mise à jour: 22 octobre 2025</p>

        <section class="legal-grid">
            <div class="panel short">
                <h3>Politique de confidentialité</h3>
                <p>Nous protégeons les données personnelles et de santé selon les normes en vigueur (RGPD, hébergement sécurisé). Les finalités et durées de conservation sont précisées dans notre politique complète.</p>
                <a class="more" href="#privacy-details">En savoir plus</a>
            </div>

            <div class="panel">
                <h3>Conditions d’utilisation</h3>
                <p>Ces conditions définissent l'usage professionnel de la plateforme MedDash par les établissements de santé. Elles couvrent les obligations de l'établissement utilisatrice, les engagements de MedDash en matière de sécurité et les limites de responsabilité.</p>
                <ul>
                    <li><strong>Habilitations</strong> — gestion des accès par l'établissement ; identifiants personnels requis.</li>
                    <li><strong>Sécurité</strong> — chiffrement, journaux d'audit et hébergement sécurisé conformes aux obligations applicables aux données de santé.</li>
                    <li><strong>SLA</strong> — disponibilités et procédures d'incident précisées contractuellement ; maintenance planifiée annoncée à l'avance.</li>
                    <li><strong>Responsabilités</strong> — l'établissement reste responsable du contenu clinique et des décisions médicales ; MedDash assure la plateforme et son intégrité technique.</li>
                </ul>
                <a class="more" href="#terms-details">Lire les détails</a>
            </div>

            <div class="panel full long">
                <h3>Droits des utilisateurs et gestion des données</h3>
                <p>Les utilisateurs et les patients bénéficient de droits encadrés par le RGPD et la réglementation relative aux données de santé. Les demandes d'accès, de rectification ou d'effacement sont traitées selon des procédures définies en collaboration avec l'établissement.</p>
                <ul>
                    <li><strong>Droit d'accès :</strong> possibilité de demander une copie des données détenues vous concernant.</li>
                    <li><strong>Droit de rectification :</strong> correction des données inexactes via les procédures internes de l'établissement.</li>
                    <li><strong>Droit à l'effacement :</strong> supprimable sous réserve des obligations légales de conservation (dossiers médicaux, archives réglementaires).</li>
                    <li><strong>Logs et traçabilité :</strong> toutes les consultations et actions sont journalisées pour garantir la traçabilité et la sécurité.</li>
                    <li><strong>Procédure de demande :</strong> les demandes doivent être adressées au DPO ou contact indiqué par l'établissement ; MedDash assiste techniquement le traitement de ces demandes.</li>
                </ul>
                <a class="more" href="#rights-details">Procédure complète</a>
            </div>
        </section>
    </div>
</main>

<?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>
