<?php

declare(strict_types=1);

namespace App\Models\Patient\Interfaces;

use App\Models\Patient\Entities\Patient;

/**
 * Interface pour la gestion des patients d'un médecin.
 *
 * Une interface définit un contrat pour les repositories qui gèrent la liste et les infos des patients.
 */
interface IPatientManagementRepository
{
    public function findById(int $id): ?Patient;

    /** @return Patient[] */
    public function getPatientsForDoctor(int $medId): array;
}
