<?php

declare(strict_types=1);

namespace App\Models\Doctor\Interfaces;

interface IDoctorValidator
{
    /**
     * Valide les données d'inscription d'un médecin.
     * @param array $data Les données brutes du formulaire.
     * @return array Une liste d'erreurs (vide si tout est valide).
     */
    public function validateRegistration(array $data): array;
}
