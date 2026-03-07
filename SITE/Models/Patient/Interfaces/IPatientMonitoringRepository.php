<?php

declare(strict_types=1);

namespace Models\Patient\Interfaces;

interface IPatientMonitoringRepository
{
    public function getChartData(int $patientId, string $typeMesure, int $limit = 50): ?array;
    public function getAllSeuilsForMetric(int $patientId, string $typeMesure): array;

}