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
     * 
     * @param array $target Données du patient cible
     * @param array $candidates Liste des candidats à comparer
     * @param int $k Nombre de voisins à retourner
     * @return array Liste des K voisins les plus proches avec leur distance
     */
    public function findNearestNeighbors(array $target, array $candidates, int $k = 5): array;
}
