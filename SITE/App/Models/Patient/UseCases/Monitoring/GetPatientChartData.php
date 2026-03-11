<?php

declare(strict_types=1);

namespace App\Models\Patient\UseCases\Monitoring;

use App\Models\Patient\Interfaces\IPatientMonitoringRepository;

final class GetPatientChartData
{
    private IPatientMonitoringRepository $repository;

    public function __construct(IPatientMonitoringRepository $repository)
    {
        $this->repository = $repository;
    }

    public function execute(int $patientId, string $typeMesure): ?array
    {
        // 1. Récupérer les données brutes (SQL)
        $data = $this->repository->getChartData($patientId, $typeMesure);

        if (!$data || empty($data['valeurs'])) {
            return null;
        }

        // 2. Vérifier qu'au moins une valeur existe
        $valeurs = $data['valeurs'];
        if (!is_array($valeurs)) {
            return null;
        }

        // 3. Récupérer la dernière valeur
        $lastValueRow = end($valeurs);
        if (!is_array($lastValueRow) || !isset($lastValueRow['valeur'])) {
            return null;
        }
        $lastValue = (float)$lastValueRow['valeur'];

        // 4. Récupérer les seuils
        $seuils = $this->repository->getAllSeuilsForMetric($patientId, $typeMesure);

        // 5. Calculer min/max pour l'échelle (logique du main)
        $vals = array_column($valeurs, 'valeur');
        $minVal = min($vals);
        $maxVal = max($vals);

        // Utiliser les seuils critiques si disponibles pour borner l'échelle
        $chartMin = isset($seuils['seuil_critique_min']) ? min((float)$seuils['seuil_critique_min'], $minVal) : $minVal;
        $chartMax = isset($seuils['seuil_critique']) ? max((float)$seuils['seuil_critique'], $maxVal) : $maxVal;

        // Marge de 10%
        $padding = ($chartMax - $chartMin) * 0.1;
        $chartMin -= $padding;
        $chartMax += $padding;

        // 6. Normaliser les valeurs entre 0 et 1
        $normalizedValues = array_map(function ($item) use ($chartMin, $chartMax) {
            $val = (float)$item['valeur'];
            if ($chartMax === $chartMin) {
                return 0.5;
            }
            return max(0, min(1, ($val - $chartMin) / ($chartMax - $chartMin)));
        }, $valeurs);

        // 7. Retourner le format attendu par le JavaScript (compatible main)
        $result = [
            'id_mesure' => $data['id_mesure'] ?? null,
            'values' => $normalizedValues,
            'lastValue' => $lastValue,
            'unit' => $data['unite'] ?? ''
        ];

        // Fusion avec les seuils
        return array_merge($result, $seuils);
    }
}
