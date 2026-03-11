<?php

declare(strict_types=1);

namespace App\Models\ConsoleLog\UseCases\Intelligence;

use App\Models\ConsoleLog\Interfaces\ILogHistoryRepository;
use App\Models\ConsoleLog\Interfaces\ITreePredictor;

/**
 * Use Case : Prédit la prochaine action probable du médecin
 */
class PredictNextAction
{
    private ILogHistoryRepository $historyRepo;
    private ITreePredictor $predictor;

    public function __construct(
        ILogHistoryRepository $historyRepo,
        ITreePredictor $predictor
    ) {
        $this->historyRepo = $historyRepo;
        $this->predictor = $predictor;
    }

    public function execute(int $medId): ?string
    {
        // 1. Récupérer l'historique récent
        $history = $this->historyRepo->getHistoryByMedId($medId, 100);

        if (empty($history)) {
            return null;
        }

        // 2. Déléguer au service de prédiction
        return $this->predictor->predict($history);
    }
}