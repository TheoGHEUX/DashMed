<?php

declare(strict_types=1);

namespace App\Models\Patient\Interfaces;

interface IDashboardLayoutRepository
{
    public function getDashboardLayout(int $patientId, int $medId): ?array;
    public function saveDashboardLayout(int $patientId, int $medId, array $config): bool;

    public function getPatientDataForSimilarity(int $patientId): ?array;
    public function getCandidatesForSimilarity(int $medId, int $excludePatientId): array;
}