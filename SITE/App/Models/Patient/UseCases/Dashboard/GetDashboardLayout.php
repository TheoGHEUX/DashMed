<?php

declare(strict_types=1);

namespace App\Models\Patient\UseCases\Dashboard;

use App\Models\Patient\Interfaces\IDashboardLayoutRepository;

/**
 * Use case pour récupérer le layout personnalisé d'un patient.
 *
 * Un use case (cas d'usage) regroupe la logique métier pour une action précise du domaine.
 * Il orchestre les appels aux repositories, services, etc., pour réaliser une tâche métier complète.
 */
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
