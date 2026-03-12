<?php

declare(strict_types=1);

namespace App\Models\Patient\UseCases\Dashboard;

use App\Models\Patient\Interfaces\IDashboardLayoutRepository;

/**
 * Use case pour sauvegarder un layout de dashboard patient.
 *
 * Un use case (cas d'usage) regroupe la logique métier pour une action précise du domaine.
 * Il orchestre les appels aux repositories, services, etc., pour réaliser une tâche métier complète.
 */
final class SaveDashboardLayout
{
    private IDashboardLayoutRepository $repository;

    public function __construct(IDashboardLayoutRepository $repository)
    {
        $this->repository = $repository;
    }

    public function execute(int $patientId, int $medId, array $config): bool
    {
        return $this->repository->saveDashboardLayout($patientId, $medId, $config);
    }
}
