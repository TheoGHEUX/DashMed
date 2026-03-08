<?php

declare(strict_types=1);

namespace App\Models\Patient\UseCases\Dashboard;

use App\Models\Patient\Interfaces\IDashboardLayoutRepository;
use App\Models\Patient\Services\PatientSimilarityService;

class SuggestLayout
{
    private IDashboardLayoutRepository $repository;
    private PatientSimilarityService $similarityService;

    public function __construct(
        IDashboardLayoutRepository $repository,
        PatientSimilarityService $similarityService
    ) {
        $this->repository = $repository;
        $this->similarityService = $similarityService;
    }

    public function execute(int $patientId, int $medId): ?array
    {
        // 1. Récupérer les données du patient cible (via Repo)
        $targetData = $this->repository->getPatientDataForSimilarity($patientId);
        if (!$targetData) return null;

        // 2. Récupérer les candidats (via Repo)
        $candidatesData = $this->repository->getCandidatesForSimilarity($medId, $patientId);
        if (empty($candidatesData)) return null;

        // 3. Calculer les plus proches (via Service)
        $nearest = $this->similarityService->findNearestNeighbors($targetData, $candidatesData, 1);

        if (empty($nearest)) return null;

        // 4. Récupérer le layout du meilleur candidat (via Repo)
        $bestMatchId = $nearest[0]['pt_id'];
        return $this->repository->getDashboardLayout($bestMatchId, $medId);
    }
}