<?php

declare(strict_types=1);

namespace App\Models\Patient\Interfaces;

/**
 * Interface pour la gestion des données de suivi patient (monitoring).
 *
 * Une interface définit un contrat pour les repositories qui gèrent les mesures et seuils des patients.
 */
interface IPatientMonitoringRepository
{
    public function getChartData(int $patientId, string $typeMesure, int $limit = 50): ?array;
    public function getAllSeuilsForMetric(int $patientId, string $typeMesure): array;
}
