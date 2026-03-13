<?php

declare(strict_types=1);

namespace App\Models\Patient\Interfaces;

use App\Models\Patient\Entities\Patient;

/**
 * Contrat pour le repository de gestion administrative des patients.
 */
interface IPatientManagementRepository
{
    /**
     * Cherche un patient par identifiant.
     * @return Patient|null
     */
    public function findById(int $id): ?Patient;

    /**
     * Retourne tous les patients suivis par un médecin.
     * @return Patient[]
     */
    public function getPatientsForDoctor(int $medId): array;
}