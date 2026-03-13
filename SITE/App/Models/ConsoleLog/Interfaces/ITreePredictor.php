<?php

declare(strict_types=1);

namespace App\Models\ConsoleLog\Interfaces;

/**
 * Interface pour le service de prédiction d'actions via arbre de décision.
 */
interface ITreePredictor
{
    /**
     * Prédit la prochaine action probable basée sur le contexte actuel.
     *
     * @param string $action    Action actuelle (ajouter, supprimer, réduire, agrandir…)
     * @param string $mesure    Type de mesure concernée
     * @param int    $heure     Heure au format 0-23
     * @param int    $position  Position/context de l'action
     * @return array            Résultat de la prédiction (clé 'prediction', 'success', etc.)
     */
    public function predict(string $action, string $mesure, int $heure, int $position): array;
}