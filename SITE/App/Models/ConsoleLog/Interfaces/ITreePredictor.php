<?php

declare(strict_types=1);

namespace App\Models\ConsoleLog\Interfaces;

/**
 * Interface pour les services de prédiction d'actions (arbre de décision).
 */
interface ITreePredictor
{
    /**
     * Prédit la prochaine action probable basée sur le contexte actuel
     *
     * @param string $action Action actuelle (ajouter, supprimer, réduire, agrandir)
     * @param string $mesure Type de mesure (ex: "Tension artérielle")
     * @param int $heure Heure de la journée (0-23)
     * @param int $position Position dans la séquence
     * @return array Résultat de la prédiction avec success, prediction, confidence, etc.
     */
    public function predict(string $action, string $mesure, int $heure, int $position): array;
}
