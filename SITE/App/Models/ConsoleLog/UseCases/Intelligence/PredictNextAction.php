<?php

declare(strict_types=1);

namespace App\Models\ConsoleLog\UseCases\Intelligence;

use App\Models\ConsoleLog\Interfaces\ILogHistoryRepository;
use App\Models\ConsoleLog\Services\TreePredictor;

class PredictNextAction
{
    private ILogHistoryRepository $repository;
    private TreePredictor $predictor;

    public function __construct(ILogHistoryRepository $repository, TreePredictor $predictor)
    {
        $this->repository = $repository;
        $this->predictor = $predictor;
    }

    public function execute(int $medId): ?string
    {
        // 1. Récupérer l'historique récent (Infrastructure)
        $history = $this->repository->getHistoryByMedId($medId, 50);

        // 2. Lancer la prédiction (Domaine / IA)
        return $this->predictor->predict($history);
    }
}