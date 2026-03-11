<?php

declare(strict_types=1);

namespace App\Models\Patient\Interfaces;

use App\Models\Patient\Entities\Patient;

interface IPatientManagementRepository
{
    public function findById(int $id): ?Patient;

    /** @return Patient[] */
    public function getPatientsForDoctor(int $medId): array;
}
