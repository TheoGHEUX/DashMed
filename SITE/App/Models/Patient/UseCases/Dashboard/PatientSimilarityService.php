<?php

declare(strict_types=1);

namespace App\Models\Patient\UseCases\Dashboard;

use App\Models\Patient\Interfaces\IPatientSimilarityService;

/**
 * Service pour le calcul de similarité entre patients (KNN).
 * Permet d’identifier les plus proches voisins via diverses caractéristiques.
 */
final class PatientSimilarityService implements IPatientSimilarityService
{
    /**
     * Algorithme KNN simple pour trouver les patients les plus proches (similaires).
     *
     * @param array $target       Patient cible
     * @param array $candidates   Patients candidats
     * @param int $k              Nombre de voisins souhaités
     * @return array              Liste triée des plus proches voisins
     */
    public function findNearestNeighbors(array $target, array $candidates, int $k = 5): array
    {
        $distances = [];

        foreach ($candidates as $candidate) {
            $score = 0;

            // 1. Âge (normalisé)
            $ageDiff = ($target['age'] - $candidate['age']) / 100;
            $score += $ageDiff * $ageDiff;

            // 2. Sexe
            if ($target['sexe'] !== $candidate['sexe']) {
                $score += 1;
            }

            // 3. Groupe sanguin
            if (
                isset($target['groupe_sanguin'], $candidate['groupe_sanguin'])
                && $target['groupe_sanguin'] !== $candidate['groupe_sanguin']
            ) {
                $score += 0.5;
            }

            // 4. Constantes vitales (distance euclidienne pondérée)
            $metrics = [
                'avg_tension' => 80,
                'avg_fc' => 95,
                'avg_temp' => 5,
                'avg_spo2' => 10
            ];

            foreach ($metrics as $key => $divider) {
                if (isset($target[$key], $candidate[$key]) && $target[$key] && $candidate[$key]) {
                    $diff = ($target[$key] - $candidate[$key]) / $divider;
                    $score += $diff * $diff;
                }
            }

            $distances[] = [
                'pt_id' => $candidate['pt_id'],
                'distance' => sqrt($score)
            ];
        }

        usort($distances, fn($a, $b) => $b['distance'] <=> $a['distance']);

        return array_slice($distances, 0, $k);
    }
}