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

    public function logAction(): void
    {
        $this->checkAuth();
        // Validation CSRF recommandée pour les logs aussi
        // if (!$this->validateApiCsrf()) return;

        $input = $this->getJsonInput();

        $success = $this->logger->execute(
            $this->getCurrentUserId(),
            $input['action'] ?? '',
            (int)($input['ptId'] ?? 0),
            (int)($input['idMesure'] ?? 0)
        );

        $this->jsonSuccess(['success' => $success]);
    }

    public function predict(): void
    {
        $this->checkAuth();

        $action = $this->predictor->execute($this->getCurrentUserId());

        $this->jsonSuccess([
            'prediction' => $action, // Peut être null
            'hasPrediction' => (bool)$action
        ]);
    }
}