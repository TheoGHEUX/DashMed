<?php

declare(strict_types=1);

namespace App\Models\Patient\UseCases\Dashboard;

use App\Models\Patient\Interfaces\IDashboardLayoutRepository;
use App\Models\Patient\Interfaces\IPatientSimilarityRepository;
use App\Models\Patient\Interfaces\IPatientSimilarityService;

/**
 * Use Case — Suggère un layout dashboard basé sur les patients les plus proches (KNN).
 */
final class SuggestLayout
{
    private IPatientSimilarityRepository $similarityRepository;
    private IPatientSimilarityService $similarityService;

    public function __construct(
        IPatientSimilarityRepository $similarityRepository,
        IPatientSimilarityService $similarityService
    ) {
        $this->similarityRepository = $similarityRepository;
        $this->similarityService = $similarityService;
    }

    /**
     * Trouve, pour un patient, le layout du patient le plus proche (avec layout renseigné), ou null sinon.
     */
    public function execute(int $patientId, int $medId): ?array
    {
        // 1. Récupérer les données du patient cible
        $targetData = $this->similarityRepository->getPatientDataForSimilarity($patientId);
        if (!$targetData) {
            return null;
        }

        // 2. Récupérer les candidats
        $candidatesData = $this->similarityRepository->getCandidatesForSimilarity($medId, $patientId);
        if (empty($candidatesData)) {
            return null;
        }

        // 3. Calcul KNN (on en prend 5 pour une liste)
        $nearest = $this->similarityService->findNearestNeighbors($targetData, $candidatesData, 5);

        if (empty($nearest)) {
            return null;
        }

        // 4. Sélection et décodage du layout du plus proche
        $bestMatchId = $nearest[0]['pt_id'];
        $bestDistance = $nearest[0]['distance'];

        $layout = null;
        foreach ($candidatesData as $candidate) {
            if ($candidate['pt_id'] === $bestMatchId) {
                $layout = json_decode($candidate['layout_config'], true);
                break;
            }
        }

        if (!$layout) {
            return null;
        }

        return [
            'similar_patient_id' => $bestMatchId,
            'distance' => round($bestDistance, 2),
            'all_similar_patients' => array_map(fn($p) => $p['pt_id'], $nearest),
            'layout' => $layout
        ];
    }
}