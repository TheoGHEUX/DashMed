<?php

declare(strict_types=1);

namespace App\Controllers\Dashboard;

use Core\Controller\AbstractController;
use App\Models\ConsoleLog\Repositories\ActionLoggerRepository;
use App\Models\ConsoleLog\Repositories\LogHistoryRepository;
use App\Models\ConsoleLog\Services\TreePredictor;
use App\Models\ConsoleLog\UseCases\Logging\LogDashboardAction;
use App\Models\ConsoleLog\UseCases\Intelligence\PredictNextAction;

final class IntelligenceApiController extends AbstractController
{
    private LogDashboardAction $logger;
    private PredictNextAction $predictor;

    public function __construct()
    {
        $this->logger = new LogDashboardAction(new ActionLoggerRepository());

        $this->predictor = new PredictNextAction(
            new LogHistoryRepository(),
            new TreePredictor()
        );
    }

    /**
     * POST /api/log-graph-action
     */
    public function logAction(): void
    {
        $this->checkAuth();
        $input = json_decode(file_get_contents('php://input'), true);

        $success = $this->logger->execute(
            $this->getCurrentUserId(),
            $input['action'] ?? '',
            (int)($input['ptId'] ?? 0),
            isset($input['idMesure']) ? (int)$input['idMesure'] : null
        );

        $this->json(['success' => $success]);
    }

    /**
     * POST /api/predict-action
     */
    public function predict(): void
    {
        $this->checkAuth();

        $action = $this->predictor->execute($this->getCurrentUserId());

        $this->json([
            'success' => true,
            'prediction' => $action,
            'hasPrediction' => (bool)$action
        ]);
    }
}