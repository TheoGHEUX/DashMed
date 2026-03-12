<?php

declare(strict_types=1);

namespace App\Controllers\Dashboard;

use Core\Controller\AbstractController;
use App\Models\ConsoleLog\Factories\ConsoleLogUseCaseFactory;
use App\Models\ConsoleLog\Repositories\ActionLoggerRepository;
use App\Models\ConsoleLog\Services\TreePredictor;
use App\Models\ConsoleLog\UseCases\Logging\LogDashboardAction;
use App\Models\ConsoleLog\UseCases\Intelligence\PredictNextAction;

/**
 * Contrôleur pour les fonctionnalités avancées du dashboard (logs et prédictions).
 *
 * Permet d'enregistrer les actions utilisateur et de prédire la prochaine action à l'aide d'un modèle IA.
 */
final class IntelligenceApiController extends AbstractController
{
    private LogDashboardAction $logger;
    private PredictNextAction $predictor;

    public function __construct()
    {
        if (class_exists(ConsoleLogUseCaseFactory::class)) {
            $this->logger = ConsoleLogUseCaseFactory::createLogDashboardAction();
            $this->predictor = ConsoleLogUseCaseFactory::createPredictNextAction();
            return;
        }

        $this->logger = new LogDashboardAction(new ActionLoggerRepository());
        $this->predictor = new PredictNextAction(new TreePredictor());
    }
    /**
     * Enregistre une action utilisateur sur le dashboard.
     *
     * Utilisé pour l'analyse des interactions et l'amélioration de l'expérience.
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
     * Prédit la prochaine action probable du médecin selon le contexte.
     *
     * Utilise un modèle IA pour suggérer l'action la plus pertinente.
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
