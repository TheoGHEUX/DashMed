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
            if (!empty($chartData[$jsKey]['donnees_normalisees'])) {
                $norm = $chartData[$jsKey]['donnees_normalisees'];
                $brutes = $chartData[$jsKey]['donnees_brutes'];
                // DERNIÈRE valeur brute affichable
                $lastReal = !empty($brutes) ? end($brutes)['valeur'] : 'null';
                $seuils = $chartData[$jsKey]['seuils'] ?? [];
                echo "window.patientChartData['$jsKey'] = {";
                echo "values: " . json_encode($norm, JSON_UNESCAPED_UNICODE) . ",";
                echo "lastValue: $lastReal,";
                echo "unit: " . json_encode($chartData[$jsKey]['info']['unite'] ?? '', JSON_UNESCAPED_UNICODE) . ",";
                echo "seuil_preoccupant:"     . json_encode($seuils['seuil_preoccupant']      ?? null) . ",";
                echo "seuil_urgent:"         . json_encode($seuils['seuil_urgent']            ?? null) . ",";
                echo "seuil_critique:"       . json_encode($seuils['seuil_critique']          ?? null) . ",";
                echo "seuil_preoccupant_min:". json_encode($seuils['seuil_preoccupant_min']   ?? null) . ",";
                echo "seuil_urgent_min:"     . json_encode($seuils['seuil_urgent_min']        ?? null) . ",";
                echo "seuil_critique_min:"   . json_encode($seuils['seuil_critique_min']      ?? null);
                echo "};\n";
            }
        }
    }
    ?>
</script>