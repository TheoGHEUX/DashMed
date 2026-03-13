<?php

declare(strict_types=1);

namespace App\Models\ConsoleLog\UseCases\Intelligence;

use App\Models\ConsoleLog\Interfaces\ITreePredictor;

/**
 * Use case: interroge le service d’IA pour prédire la prochaine action probable du médecin.
 */
final class PredictNextAction
{
    private ITreePredictor $predictor;

    public function __construct(ITreePredictor $predictor)
    {
        $this->predictor = $predictor;
    }

    /**
     * Exécute la prédiction du modèle, en passant tous les paramètres nécessaires pour
     * obtenir la prochaine suggestion pertinente (action, mesure, heure, position…).
     */
    public function execute(string $action, string $mesure, int $heure, int $position): array
    {
        return $this->predictor->predict($action, $mesure, $heure, $position);
    }
}