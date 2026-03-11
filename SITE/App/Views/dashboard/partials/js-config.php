<script>
    window.csrfToken = <?= json_encode(\Core\Csrf::token(), JSON_HEX_TAG | JSON_HEX_AMP) ?>;
    window.activePatient = <?= json_encode($patient ?? [], JSON_UNESCAPED_UNICODE) ?>;
    window.patientChartData = {};
    <?php
    $metricsMap = [
            'blood-pressure'    => 'Tension artérielle',
            'heart-rate'        => 'Fréquence cardiaque',
            'respiration'       => 'Fréquence respiratoire',
            'temperature'       => 'Température corporelle',
            'glucose-trend'     => 'Glycémie',
            'weight'            => 'Poids',
            'oxygen-saturation' => 'Saturation en oxygène',
    ];

    if (!empty($chartData)) {
        foreach ($metricsMap as $jsKey => $libelle) {
            // Nouveau format retourné par GetPatientChartData::execute()
            if (!empty($chartData[$jsKey]['values'])) {
                $metric = $chartData[$jsKey];

                echo "window.patientChartData['$jsKey'] = {";
                echo "id_mesure: " . json_encode($metric['id_mesure'] ?? null) . ",";
                echo "values: " . json_encode($metric['values'], JSON_UNESCAPED_UNICODE) . ",";
                echo "lastValue: " . ($metric['lastValue'] ?? 'null') . ",";
                echo "unit: " . json_encode($metric['unit'] ?? '', JSON_UNESCAPED_UNICODE) . ",";
                echo "seuil_preoccupant:"     . json_encode($metric['seuil_preoccupant']      ?? null) . ",";
                echo "seuil_urgent:"         . json_encode($metric['seuil_urgent']            ?? null) . ",";
                echo "seuil_critique:"       . json_encode($metric['seuil_critique']          ?? null) . ",";
                echo "seuil_preoccupant_min:" . json_encode($metric['seuil_preoccupant_min']   ?? null) . ",";
                echo "seuil_urgent_min:"     . json_encode($metric['seuil_urgent_min']        ?? null) . ",";
                echo "seuil_critique_min:"   . json_encode($metric['seuil_critique_min']      ?? null);
                echo "};\n";
            }
        }
    }
    ?>
</script>