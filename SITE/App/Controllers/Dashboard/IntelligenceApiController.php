<?php

declare(strict_types=1);

namespace App\Controllers\Dashboard;

use Core\Controller\AbstractController;
use App\Models\ConsoleLog\Factories\ConsoleLogUseCaseFactory;
use App\Models\ConsoleLog\UseCases\Logging\LogDashboardAction;
use App\Models\ConsoleLog\UseCases\Intelligence\PredictNextAction;

final class IntelligenceApiController extends AbstractController
{
    private LogDashboardAction $logger;
    private PredictNextAction $predictor;

    public function __construct()
    {
        $this->logger = ConsoleLogUseCaseFactory::createLogDashboardAction();
        $this->predictor = ConsoleLogUseCaseFactory::createPredictNextAction();
    }

    /**
     * POST /api/log-graph-action
     */
    public function logAction(): void
    {
        $this->checkAuth();
        $this->validateApiCsrf();
        
        $input = json_decode(file_get_contents('php://input'), true);

        $ptId = isset($input['ptId']) && $input['ptId'] > 0 ? (int)$input['ptId'] : null;
        $idMesure = isset($input['idMesure']) && $input['idMesure'] > 0 ? (int)$input['idMesure'] : null;

        $success = $this->logger->execute(
            $this->getCurrentUserId(),
            $input['action'] ?? '',
            $ptId,
            $idMesure
        );

        $this->json(['success' => $success]);
    }

    /**
     * POST /api/predict-action
     * Prédit la prochaine action du médecin en fonction du contexte actuel
     */
    public function predict(): void
    {
        $this->checkAuth();
        $this->validateApiCsrf();

        $input = json_decode(file_get_contents('php://input'), true);
        
        $action = $input['action'] ?? null;
        $mesure = $input['mesure'] ?? null;
        $heure = $input['heure'] ?? (int) date('G');
        $position = $input['position'] ?? 0;

        // Validation des paramètres requis
        if (!$action || !$mesure) {
            $this->json(['success' => false, 'error' => 'Paramètres action et mesure requis'], 400);
            return;
        }

        // Validation de l'action
        $validActions = ['ajouter', 'supprimer', 'réduire', 'agrandir'];
        if (!in_array($action, $validActions, true)) {
            $this->json(['success' => false, 'error' => 'Action invalide'], 400);
            return;
        }

        try {
            $result = $this->predictor->execute($action, $mesure, (int) $heure, (int) $position);
            $this->json($result);
        } catch (\Throwable $e) {
            error_log('[PREDICT] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            $this->json(['success' => false, 'error' => 'Modèle IA non disponible ou erreur interne'], 503);
        }
    }
}
