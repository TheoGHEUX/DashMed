<?php

declare(strict_types=1);

namespace Models\Patient\UseCases\Dashboard;

use Models\Patient\Interfaces\IDashboardLayoutRepository;

class SaveDashboardLayout
{
    private IDashboardLayoutRepository $repository;

    public function __construct(IDashboardLayoutRepository $repository)
    {
        $this->repository = $repository;
    }

    public function execute(int $patientId, int $medId, array $config): bool
    {
        // On pourrait ajouter ici une validation de la structure du JSON $config
        return $this->repository->saveDashboardLayout($patientId, $medId, $config);
    }
}