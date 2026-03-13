<?php

declare(strict_types=1);

namespace App\Models\Patient\Interfaces;

/**
 * Contrat pour un repository de données de suivi patient (monitoring).
 */
interface IPatientMonitoringRepository
{
    /**
     * Retourne les mesures d’un certain type pour un patient.
     */
    public function getChartData(int $patientId, string $typeMesure, int $limit = 50): ?array;

    /**
     * Retourne tous les seuils paramétrés pour une métrique précise.
     */
    public function getAllSeuilsForMetric(int $patientId, string $typeMesure): array;
}