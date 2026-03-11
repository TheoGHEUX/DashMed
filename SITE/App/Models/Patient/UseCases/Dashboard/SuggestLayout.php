<?php

declare(strict_types=1);

namespace App\Models\Patient\UseCases\Dashboard;

use App\Models\Patient\Interfaces\IDashboardLayoutRepository;
use App\Models\Patient\Interfaces\IPatientSimilarityRepository;
use App\Models\Patient\Interfaces\IPatientSimilarityService;

/**
 * Use Case : Suggère un layout de dashboard basé sur des patients similaires
 */
final class SuggestLayout
{
    private IDashboardLayoutRepository $layoutRepository;
    private IPatientSimilarityRepository $similarityRepository;
    private IPatientSimilarityService $similarityService;

    public function __construct(
        IDashboardLayoutRepository $layoutRepository,
        IPatientSimilarityRepository $similarityRepository,
        IPatientSimilarityService $similarityService
    ) {
        $this->layoutRepository = $layoutRepository;
        $this->similarityRepository = $similarityRepository;
        $this->similarityService = $similarityService;
    }

    public function execute(int $patientId, int $medId): ?array
    {
        // 1. Récupérer les données du patient cible (via Repo de similarité)
        $targetData = $this->similarityRepository->getPatientDataForSimilarity($patientId);
        if (!$targetData) return null;

        // 2. Récupérer les candidats (via Repo de similarité)
        $candidatesData = $this->similarityRepository->getCandidatesForSimilarity($medId, $patientId);
        if (empty($candidatesData)) return null;

        // 3. Calculer les K plus proches (on en prend 5 pour avoir une liste)
        $nearest = $this->similarityService->findNearestNeighbors($targetData, $candidatesData, 5);

        if (empty($nearest)) return null;

        // 4. Trouver le layout du patient le plus similaire
        // Le layout_config est déjà dans candidatesData, pas besoin de refaire une requête
        $bestMatchId = $nearest[0]['pt_id'];
        $bestDistance = $nearest[0]['distance'];
        
        $layout = null;
        foreach ($candidatesData as $candidate) {
            if ($candidate['pt_id'] === $bestMatchId) {
                // Décoder le JSON du layout_config
                $layout = json_decode($candidate['layout_config'], true);
                break;
            }
        }

        if (!$layout) return null;

        // Retourner toutes les informations comme dans main
        return [
            'similar_patient_id' => $bestMatchId,
            'distance' => round($bestDistance, 2),
            'all_similar_patients' => array_map(fn($p) => $p['pt_id'], $nearest),
            'layout' => $layout
        ];
    }
}