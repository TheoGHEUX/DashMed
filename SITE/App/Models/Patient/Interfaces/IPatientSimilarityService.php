<?php

declare(strict_types=1);

namespace App\Models\Patient\Interfaces;

/**
 * Interface pour le service de calcul de similarité entre patients
 */
interface IPatientSimilarityService
{
    /**
     * Trouve les patients les plus proches selon l'algorithme KNN
     */
    public function findNearestNeighbors(array $target, array $candidates, int $k = 5): array;
}
