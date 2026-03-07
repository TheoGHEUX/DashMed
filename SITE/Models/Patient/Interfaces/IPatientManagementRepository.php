<?php

declare(strict_types=1);

namespace Models\Patient\Interfaces;

use Models\Patient\Entities\Patient;

interface IPatientManagementRepository
{
    public function findById(int $id): ?Patient;

    /** @return Patient[] */
    public function getPatientsForDoctor(int $medId): array;
}