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
 * Contrôleur API pour la journalisation et l’intelligence sur le dashboard.
 *
 * - Permet d’enregistrer les actions réalisées par le médecin sur les graphiques du dashboard.
 * - Peut prédire la prochaine action à exécuter .
 * - Compatible avec une architecture évolutive (usecases via factory ou fallback manuel).
 */
final class IntelligenceApiController extends AbstractController
{
    private LogDashboardAction $logger;
    private PredictNextAction $predictor;

    /**
     * Prépare les usecases de log et de prédiction..
     */
    public function __construct()
    {
        if (class_exists(ConsoleLogUseCaseFactory::class)) {
            $this->logger = ConsoleLogUseCaseFactory::createLogDashboardAction();
            $this->predictor = ConsoleLogUseCaseFactory::createPredictNextAction();
            return;
        }


    }

    /**
     * Enregistre une action sur les graphiques (log d’activité du médecin).
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
     * Prédit la prochaine action du médecin sur le dashboard.
     *
     * Utilise le contexte (action en cours, type de mesure, heure, position) pour suggérer la suite.
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

        // Vérifie que tous les paramètres nécessaires sont présents
        if (!$action || !$mesure) {
            $this->json(['success' => false, 'error' => 'Paramètres action et mesure requis'], 400);
            return;
        }

        // Vérifie que l'action demandée fait partie des actions autorisées
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