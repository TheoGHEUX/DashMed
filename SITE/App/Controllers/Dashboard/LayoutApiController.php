<?php

declare(strict_types=1);

namespace App\Controllers\Dashboard;

use Core\Controller\AbstractController;
use App\Models\Patient\Factories\PatientUseCaseFactory;
use App\Models\Patient\Repositories\DashboardLayoutRepository;
use App\Models\Patient\UseCases\Dashboard\PatientSimilarityService;
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
        if (class_exists(PatientUseCaseFactory::class)) {
            $this->getLayout = PatientUseCaseFactory::createGetDashboardLayout();
            $this->saveLayout = PatientUseCaseFactory::createSaveDashboardLayout();
            $this->suggestLayout = PatientUseCaseFactory::createSuggestLayout();
            return;
        }

        // Fallback compatibilité déploiement ancien/incomplet
        $repo = new DashboardLayoutRepository();
        $similarityService = new PatientSimilarityService();
        $this->getLayout = new GetDashboardLayout($repo);
        $this->saveLayout = new SaveDashboardLayout($repo);
        
        // SuggestLayout utilise uniquement l'interface IPatientSimilarity
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

        // Signature correcte : execute(int $patientId, int $medId)
        $layout = $this->getLayout->execute($ptId, $medId);

        // Si null, le JavaScript utilisera le layout par défaut ou fera une suggestion IA
        $this->json([
            'success' => true,
            'layout' => $layout,
            'isDefault' => $layout === null
        ]);
    }

    /**
     * POST /api/save-dashboard-layout
     */
    public function save(): void
    {
        $this->checkAuth();
        $this->validateApiCsrf();

        // Récupérer le JSON envoyé par fetch()
        $input = $this->getJsonInput();

        $ptId = (int)($input['ptId'] ?? 0);
        $config = $input['config'] ?? null;
        $medId = $this->getCurrentUserId();

        if (!$ptId || !is_array($config)) {
            $this->json(['success' => false, 'error' => 'Données invalides'], 400);
            return;
        }

        // Valider la structure
        if (!isset($config['visible']) || !is_array($config['visible'])) {
            $this->json(['success' => false, 'error' => 'Configuration invalide'], 400);
            return;
        }

        // Signature correcte : execute(int $patientId, int $medId, array $config)
        $success = $this->saveLayout->execute($ptId, $medId, $config);

        if ($success) {
            $this->json(['success' => true]);
        } else {
            $this->json(['success' => false, 'error' => 'Échec de la sauvegarde - Le médecin ne suit peut-être pas ce patient'], 500);
        }
    }

    /**
     * GET /api/suggest-layout
     */
    public function suggest(): void
    {
        $this->checkAuth();
        $ptId = (int)($_GET['ptId'] ?? 0);
        $medId = $this->getCurrentUserId();

        if (!$ptId) {
            $this->json(['success' => false, 'error' => 'ID patient manquant'], 400);
            return;
        }

        $result = $this->suggestLayout->execute($ptId, $medId);

        if (!empty($result)) {
            $this->json([
                'success' => true,
                'suggestion' => $result
            ]);
        } else {
            $this->json([
                'success' => false,
                'message' => 'Aucune suggestion disponible - Aucun patient similaire avec un agencement personnalisé'
            ]);
        }
    }

    /**
     * GET /api/ai-availability
     * Vérifie si l'IA est disponible
     */
    public function checkAvailability(): void
    {
        $this->json([
            'available' => true,
            'implementation' => 'PHP KNN Algorithm',
            'message' => 'IA intégrée, aucune installation requise'
        ]);
    }
}
