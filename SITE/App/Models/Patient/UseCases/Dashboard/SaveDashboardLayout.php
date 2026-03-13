<?php

declare(strict_types=1);

namespace App\Models\Patient\UseCases\Dashboard;

use App\Models\Patient\Interfaces\IDashboardLayoutRepository;

/**
 * Use Case – Permet de sauvegarder l’agencement du dashboard pour un patient.
 */
final class SaveDashboardLayout
{
    private IDashboardLayoutRepository $repository;

    public function __construct(IDashboardLayoutRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Sauvegarde le layout sous forme de tableau.
     */
    public function execute(int $patientId, int $medId, array $config): bool
    {
        return $this->repository->saveDashboardLayout($patientId, $medId, $config);
    }
}