<?php

declare(strict_types=1);

namespace App\Controllers\Dashboard;

use Core\Controller\AbstractController;
use Models\Patient\Repositories\DashboardLayoutRepository;
use Models\Patient\Services\PatientSimilarityService; // Le Service KNN
use Models\Patient\UseCases\Dashboard\GetDashboardLayout;
use Models\Patient\UseCases\Dashboard\SaveDashboardLayout;
use Models\Patient\UseCases\Dashboard\SuggestLayout;

final class LayoutApiController extends AbstractController
{
    private GetDashboardLayout $getLayout;
    private SaveDashboardLayout $saveLayout;
    private SuggestLayout $suggestLayout;

    public function __construct()
    {
        // 1. Instanciation des dépendances de bas niveau
        $repo = new DashboardLayoutRepository();
        $similarityService = new PatientSimilarityService(); // On instancie le service

        // 2. Injection dans les UseCases
        $this->getLayout = new GetDashboardLayout($repo);
        $this->saveLayout = new SaveDashboardLayout($repo);

        // C'est ici que ça change : on injecte Repo + Service
        $this->suggestLayout = new SuggestLayout($repo, $similarityService);
    }

    // ... Tes méthodes get(), save() et suggest() restent identiques ...

    public function suggest(): void
    {
        $this->checkAuth(); // Ou checkAuthApi() selon ton AbstractController
        $ptId = (int)($_GET['ptId'] ?? 0);

        // Le UseCase s'occupe de tout :
        // SQL Data -> Service KNN -> SQL Layout -> Résultat
        $layout = $this->suggestLayout->execute($ptId, $this->getCurrentUserId());

        if ($layout) {
            // Helper JSON de ton AbstractController
            $this->jsonSuccess(['layout' => $layout]);
        } else {
            $this->jsonError('Aucune suggestion disponible.', 404);
        }
    }
}