<?php

declare(strict_types=1);

namespace App\Models\ConsoleLog\UseCases\Intelligence;

use App\Models\ConsoleLog\Interfaces\ITreePredictor;

/**
 * Use Case : Prédit la prochaine action probable du médecin
 */
class PredictNextAction
{
    private ITreePredictor $predictor;

    public function __construct(ITreePredictor $predictor)
    {
        $this->predictor = $predictor;
    }

    /**
     * @param string $action Action actuelle
     * @param string $mesure Type de mesure
     * @param int $heure Heure de la journée (0-23)
     * @param int $position Position dans la séquence
     * @return array Résultat de la prédiction
     */
    public function execute(string $action, string $mesure, int $heure, int $position): array
    {
        return $this->predictor->predict($action, $mesure, $heure, $position);
    }
}