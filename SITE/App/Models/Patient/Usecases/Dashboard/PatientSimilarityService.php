<?php

declare(strict_types=1);

namespace App\Models\Patient\Services;

class PatientSimilarityService
{
    /**
     * Algorithme KNN : Trouve les patients les plus proches mathématiquement.
     */
    public function findNearestNeighbors(array $target, array $candidates, int $k = 5): array
    {
        $distances = [];

        foreach ($candidates as $candidate) {
            $score = 0;

            // 1. Âge (Normalisé sur 100 ans)
            $ageDiff = ($target['age'] - $candidate['age']) / 100;
            $score += $ageDiff * $ageDiff;

            // 2. Sexe (Binaire)
            if ($target['sexe'] !== $candidate['sexe']) {
                $score += 1;
            }

            // 3. Constantes vitales (Distance Euclidienne pondérée)
            // On compare les moyennes si elles existent
            $metrics = [
                'avg_tension' => 80, // Écart type approximatif
                'avg_fc' => 40,
                'avg_spo2' => 10,
                'avg_temp' => 2
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

        // Tri croissant (plus petite distance = plus grande similarité)
        usort($distances, fn($a, $b) => $a['distance'] <=> $b['distance']);

        return array_slice($distances, 0, $k);
    }
}