<?php

declare(strict_types=1);

namespace App\Models\Patient\UseCases\Management;

use App\Models\Patient\Interfaces\IPatientManagementRepository;
use App\Models\Patient\Entities\Patient;

/**
 * Use case pour récupérer la liste des patients suivis par un médecin.
 *
 * Un use case (cas d'usage) regroupe la logique métier pour une action précise du domaine.
 * Il orchestre les appels aux repositories, services, etc., pour réaliser une tâche métier complète.
 */
final class GetDoctorPatients
{
    private IPatientManagementRepository $repository;

    public function __construct(IPatientManagementRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return Patient[]
     */
    public function execute(int $medId): array
    {
        return $this->repository->getPatientsForDoctor($medId);
    }
}
