<?php

declare(strict_types=1);

namespace App\Models\Patient\UseCases\Management;

use App\Models\Patient\Interfaces\IPatientManagementRepository;
use App\Models\Patient\Entities\Patient;

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
