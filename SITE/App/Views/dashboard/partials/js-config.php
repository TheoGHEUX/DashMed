<?php
/**
 * Partial : Configuration JavaScript
 *
 * Injecte les variables PHP (Tokens, Données patient) dans le scope global JS (window).
 *
 * @package Views/Dashboard/Partials
 */
?>
<script>
    window.csrfToken = <?= json_encode(\Core\Csrf::token(), JSON_HEX_TAG | JSON_HEX_AMP) ?>;
    window.patientChartData = <?= json_encode($chartData ?? [], JSON_UNESCAPED_UNICODE) ?>;
    window.activePatient = <?= json_encode($patient ?? [], JSON_UNESCAPED_UNICODE) ?>;
</script>