<?php

declare(strict_types=1);

namespace App\Models\ConsoleLog\Services;

use App\Models\ConsoleLog\Interfaces\ITreePredictor;

/**
 * Prédicteur d'actions basé sur un arbre de décision exporté en JSON.
 * Remplace l'appel Python pour des prédictions instantanées en PHP pur.
 */
final class TreePredictor implements ITreePredictor
{
    private array $model;

    private const ACTION_MAP = [
        'ajouter'   => 0,
        'supprimer' => 1,
        'réduire'   => 2,
        'agrandir'  => 3,
    ];

    public function __construct(?string $modelPath = null)
    {
        // Chemin par défaut vers le modèle JSON
        if ($modelPath === null) {
            // Depuis SITE/App/Models/ConsoleLog/Services, on remonte de 4 niveaux vers SITE/
            $modelPath = dirname(__DIR__, 4) . '/storage/model.json';
        }

        if (!file_exists($modelPath)) {
            throw new \RuntimeException("Modèle IA non trouvé : $modelPath");
        }

        $json = file_get_contents($modelPath);
        if ($json === false) {
            throw new \RuntimeException('Impossible de lire le modèle JSON');
        }

        $decoded = json_decode($json, true);
        if (!is_array($decoded)) {
            throw new \RuntimeException('Modèle JSON invalide');
        }

        $this->model = $decoded;
    }

    /**
     * Prédit la prochaine action à partir de l'action et mesure courantes.
     *
     * @param string $action Action actuelle (ajouter, supprimer, réduire, agrandir)
     * @param string $mesure Type de mesure (ex: "Tension artérielle")
     * @param int $heure Heure de la journée (0-23)
     * @param int $position Position dans la séquence
     * @return array{success: bool, prediction?: array, confidence?: float, top_predictions?: array, error?: string}
     */
    public function predict(string $action, string $mesure, int $heure, int $position): array
    {
        // Validation de l'action
        if (!isset(self::ACTION_MAP[$action])) {
            return ['success' => false, 'error' => "Action inconnue : {$action}"];
        }

        // Validation de la mesure
        $mesureClasses = $this->model['mesure_classes'];
        $mesureIdx = array_search($mesure, $mesureClasses, true);
        if ($mesureIdx === false) {
            return ['success' => false, 'error' => "Mesure inconnue : {$mesure}"];
        }

        // Construction du vecteur de features
        $features = [
            self::ACTION_MAP[$action],
            $mesureIdx,
            $heure,
            $position,
        ];

        // Parcourir l'arbre pour chaque output (action, mesure)
        $actionProbas = $this->traverse($this->model['tree']['action'], $features);
        $mesureProbas = $this->traverse($this->model['tree']['mesure'], $features);

        $actionMapInv = $this->model['action_map_inv'];
        $actionClasses = $this->model['action_classes'];
        $mesureOutputClasses = $this->model['mesure_output_classes'];

        // Trier par probabilité décroissante
        arsort($actionProbas);
        arsort($mesureProbas);

        $topActionKeys = array_keys($actionProbas);
        $topMesureKeys = array_keys($mesureProbas);

        $bestActionIdx = $topActionKeys[0];
        $bestMesureIdx = $topMesureKeys[0];

        $bestAction = $actionMapInv[(string) $actionClasses[$bestActionIdx]] ?? 'ajouter';
        $bestMesure = $mesureClasses[$mesureOutputClasses[$bestMesureIdx]] ?? $mesure;

        $actionConf = $actionProbas[$bestActionIdx];
        $mesureConf = $mesureProbas[$bestMesureIdx];
        $confidence = round($actionConf * $mesureConf, 3);

        // Top combinaisons (top 3 actions × top 3 mesures)
        $topPredictions = [];
        $topActions = array_slice($topActionKeys, 0, 3, true);
        $topMesures = array_slice($topMesureKeys, 0, 3, true);

        foreach ($topActions as $aIdx) {
            foreach ($topMesures as $mIdx) {
                $aProb = $actionProbas[$aIdx];
                $mProb = $mesureProbas[$mIdx];
                $combined = round($aProb * $mProb, 3);
                if ($combined < 0.05) {
                    continue;
                }
                $topPredictions[] = [
                    'action'      => $actionMapInv[(string) $actionClasses[$aIdx]] ?? 'ajouter',
                    'mesure'      => $mesureClasses[$mesureOutputClasses[$mIdx]] ?? $mesure,
                    'probability' => $combined,
                ];
            }
        }

        usort($topPredictions, fn(array $a, array $b) => $b['probability'] <=> $a['probability']);
        $topPredictions = array_slice($topPredictions, 0, 3);

        if (empty($topPredictions)) {
            return ['success' => false, 'error' => 'Aucune prédiction suffisamment fiable'];
        }

        return [
            'success'    => true,
            'prediction' => ['action' => $bestAction, 'mesure' => $bestMesure],
            'confidence' => $confidence,
            'details'    => [
                'action_confidence' => round($actionConf, 3),
                'mesure_confidence' => round($mesureConf, 3),
            ],
            'top_predictions' => $topPredictions,
        ];
    }

    /**
     * Parcourt récursivement un nœud de l'arbre et retourne les probabilités.
     *
     * @param array $node Nœud actuel de l'arbre
     * @param array $features Vecteur de features [action_idx, mesure_idx, heure, position]
     * @return array Liste des probabilités pour chaque classe
     */
    private function traverse(array $node, array $features): array
    {
        if ($node['leaf']) {
            return $node['probas'];
        }

        if ($features[$node['feature']] <= $node['threshold']) {
            return $this->traverse($node['left'], $features);
        }
        return $this->traverse($node['right'], $features);
    }
}