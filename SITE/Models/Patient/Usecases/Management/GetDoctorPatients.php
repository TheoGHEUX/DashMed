<?php

declare(strict_types=1);

namespace Models\Patient\UseCases\Management;

use Models\Patient\Interfaces\IPatientManagementRepository;
use Models\Patient\Entities\Patient;

class GetDoctorPatients
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
