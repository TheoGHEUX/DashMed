<?php

declare(strict_types=1);

namespace App\Models\Patient\UseCases\Dashboard;

use App\Models\Patient\Interfaces\IDashboardLayoutRepository;

/**
 * Use Case – Récupère le layout du dashboard pour un patient donné et un médecin.
 */
final class GetDashboardLayout
{
    private IDashboardLayoutRepository $repository;

    public function __construct(IDashboardLayoutRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Retourne le layout enregistré (ou null si absence)
     */
    public function execute(int $patientId, int $medId): ?array
    {
        return $this->repository->getDashboardLayout($patientId, $medId);
    }
}