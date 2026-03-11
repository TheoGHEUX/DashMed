<?php

declare(strict_types=1);

namespace App\Models\Patient\UseCases\Dashboard;

use App\Models\Patient\Interfaces\IDashboardLayoutRepository;

final class GetDashboardLayout
{
    private IDashboardLayoutRepository $repository;

    public function __construct(IDashboardLayoutRepository $repository)
    {
        $this->repository = $repository;
    }

    public function execute(int $patientId, int $medId): ?array
    {
        // Retourne le layout personnalisé ou null si aucun
        return $this->repository->getDashboardLayout($patientId, $medId);
    }
}