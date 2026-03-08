<?php

declare(strict_types=1);

namespace App\Models\Patient\UseCases\Monitoring;

use App\Models\Patient\Interfaces\IPatientMonitoringRepository;

class GetPatientChartData
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

        // 2. Récupérer les seuils (SQL)
        $seuils = $this->repository->getAllSeuilsForMetric($patientId, $typeMesure);

        // 3. LOGIQUE MÉTIER : Calculer Min/Max pour l'échelle du graphique
        $vals = array_column($data['valeurs'], 'valeur');

        // On prend les seuils critiques comme bornes par défaut si les valeurs sont "normales"
        $min = isset($seuils['seuil_critique_min']) ? (float)$seuils['seuil_critique_min'] : min($vals) * 0.9;
        $max = isset($seuils['seuil_critique']) ? (float)$seuils['seuil_critique'] : max($vals) * 1.1;

        // Si une valeur réelle dépasse les seuils, on agrandit l'échelle
        $globalMin = min(array_merge($vals, [$min]));
        $globalMax = max(array_merge($vals, [$max]));

        // Marge esthétique de 10%
        $padding = ($globalMax - $globalMin) * 0.1;
        $chartMin = $globalMin - $padding;
        $chartMax = $globalMax + $padding;

        // 4. LOGIQUE MÉTIER : Normalisation (0 à 1) pour Chart.js
        // C'est la méthode qu'on a retirée du Repository !
        $normalizedValues = array_map(function ($item) use ($chartMin, $chartMax) {
            $val = (float)$item['valeur'];
            if ($chartMax === $chartMin) return 0.5;
            // Formule mathématique : (x - min) / (max - min)
            return max(0, min(1, ($val - $chartMin) / ($chartMax - $chartMin)));
        }, $data['valeurs']);

        // 5. Construction de la réponse finale
        return [
            'info' => [
                'id_mesure' => $data['id_mesure'],
                'type' => $data['type_mesure'],
                'unite' => $data['unite']
            ],
            'seuils' => $seuils,
            'echelle' => ['min' => $chartMin, 'max' => $chartMax],
            'donnees_brutes' => $data['valeurs'],
            'donnees_normalisees' => $normalizedValues
        ];
    }
}