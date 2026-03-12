<?php

declare(strict_types=1);

namespace App\Models\Patient\Interfaces;

/**
 * Interface pour les opérations de similarité entre patients.
 *
 * Une interface définit un contrat pour les repositories qui gèrent la comparaison de similarité.
 * Séparée de IDashboardLayoutRepository pour respecter les principes de la clean architecture.
 */
interface IPatientSimilarityRepository
{
    /**
     * Récupère les données nécessaires au calcul de similarité pour un patient
     */
    public function getPatientDataForSimilarity(int $patientId): ?array;

    /**
     * Récupère les candidats potentiels pour la comparaison de similarité
     */
    public function getCandidatesForSimilarity(int $medId, int $excludePatientId): array;
}
