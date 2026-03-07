<?php

declare(strict_types=1);

namespace Models\Patient\UseCases\Dashboard;

use Models\Patient\Interfaces\IDashboardLayoutRepository;

class GetDashboardLayout
{
    private IDashboardLayoutRepository $repository;

    public function __construct(IDashboardLayoutRepository $repository)
    {
        $this->repository = $repository;
    }

    public function execute(int $patientId, int $medId): array
    {
        $layout = $this->repository->getDashboardLayout($patientId, $medId);

        // Si pas de layout perso, on retourne une configuration par défaut
        return $layout ?? [
            'widgets' => [],
            'theme' => 'default'
        ];
    }
}