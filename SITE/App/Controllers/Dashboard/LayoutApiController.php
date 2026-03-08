<?php

declare(strict_types=1);

namespace App\Controllers\Dashboard;

use Core\Controller\AbstractController;
use App\Models\Patient\Repositories\DashboardLayoutRepository;
use App\Models\Patient\Services\PatientSimilarityService;
use App\Models\Patient\UseCases\Dashboard\GetDashboardLayout;
use App\Models\Patient\UseCases\Dashboard\SaveDashboardLayout;
use App\Models\Patient\UseCases\Dashboard\SuggestLayout;

final class LayoutApiController extends AbstractController
{
    private GetDashboardLayout $getLayout;
    private SaveDashboardLayout $saveLayout;
    private SuggestLayout $suggestLayout;

    public function __construct()
    {
        $repo = new DashboardLayoutRepository();
        $similarityService = new PatientSimilarityService();

        $this->getLayout = new GetDashboardLayout($repo);
        $this->saveLayout = new SaveDashboardLayout($repo);
        $this->suggestLayout = new SuggestLayout($repo, $similarityService);
    }

    /**
     * GET /api/dashboard-layout
     */
    public function load(): void
    {
        $this->checkAuth();
        $ptId = (int)($_GET['ptId'] ?? 0);
        $medId = $this->getCurrentUserId();

        if (!$ptId) {
            $this->json(['success' => false, 'error' => 'Patient ID manquant']);
            return;
        }

        $layout = $this->getLayout->execute($medId, $ptId);

        if ($layout) {
            $this->json(['success' => true, 'layout' => $layout]);
        } else {
            // Pas de layout personnalisé, succès quand même (le JS utilisera le défaut)
            $this->json(['success' => true, 'layout' => null]);
        }
    }

    /**
     * POST /api/save-dashboard-layout
     */
    public function save(): void
    {
        $this->checkAuth();

        // Récupérer le JSON envoyé par fetch()
        $input = json_decode(file_get_contents('php://input'), true);

        $ptId = (int)($input['ptId'] ?? 0);
        $config = $input['config'] ?? null;
        $medId = $this->getCurrentUserId();

        if (!$ptId || !$config) {
            $this->json(['success' => false, 'error' => 'Données invalides'], 400);
            return;
        }

        $this->saveLayout->execute($medId, $ptId, $config);

        $this->json(['success' => true]);
    }

    /**
     * GET /api/suggest-layout
     */
    public function suggest(): void
    {
        $this->checkAuth();
        $ptId = (int)($_GET['ptId'] ?? 0);

        $layout = $this->suggestLayout->execute($ptId, $this->getCurrentUserId());

        if ($layout) {
            $this->json(['success' => true, 'suggestion' => ['layout' => $layout]]);
        } else {
            $this->json(['success' => false, 'message' => 'Aucune suggestion disponible']);
        }
    }
}