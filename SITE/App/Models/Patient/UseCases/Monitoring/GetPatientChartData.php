<?php

declare(strict_types=1);

namespace App\Models\Patient\UseCases\Monitoring;

use App\Models\Patient\Interfaces\IPatientMonitoringRepository;

/**
 * Use Case — Récupère les séries de mesure d’un patient pour une métrique donnée,
 * avec valeurs normalisées, seuils et borne min/max pour graphiques.
 */
final class GetPatientChartData
{
    private IPatientMonitoringRepository $repository;

    public function __construct(IPatientMonitoringRepository $repository)
    {
        $this->repository = $repository;
    }

    public function execute(int $patientId, string $typeMesure): ?array
    {
        // 1. Récupére les données
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

        // 4. Récupérer les seuils paramétrés pour cette métrique/patient
        $seuils = $this->repository->getAllSeuilsForMetric($patientId, $typeMesure);

        // 5. Calcul min/max (pour bornes graphiques)
        $vals = array_column($valeurs, 'valeur');
        $minVal = min($vals);
        $maxVal = max($vals);

        $chartMin = isset($seuils['seuil_critique_min']) ? min((float)$seuils['seuil_critique_min'], $minVal) : $minVal;
        $chartMax = isset($seuils['seuil_critique']) ? max((float)$seuils['seuil_critique'], $maxVal) : $maxVal;

        $padding = ($chartMax - $chartMin) * 0.1;
        $chartMin -= $padding;
        $chartMax += $padding;

        // 6. Normalisation des valeurs entre 0 et 1
        $normalizedValues = array_map(function ($item) use ($chartMin, $chartMax) {
            $val = (float)$item['valeur'];
            if ($chartMax === $chartMin) {
                return 0.5;
            }
            return max(0, min(1, ($val - $chartMin) / ($chartMax - $chartMin)));
        }, $valeurs);
        $result = [
            'id_mesure' => $data['id_mesure'] ?? null,
            'values' => $normalizedValues,
            'lastValue' => $lastValue,
            'unit' => $data['unite'] ?? ''
        ];

        // Fusion tous les seuils, bornes, etc.
        return array_merge($result, $seuils);
    }
}