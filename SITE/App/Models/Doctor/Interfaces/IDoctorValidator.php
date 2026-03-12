<?php

declare(strict_types=1);

namespace App\Models\Doctor\Interfaces;

/**
 * Interface pour la validation des données médecin.
 * 
 * Une interface définit un contrat : elle liste les méthodes qu'une classe doit implémenter.
 * Cela permet de garantir que plusieurs classes différentes respectent la même structure,
 * ce qui facilite la maintenance, les tests et l'évolution du code.
 */
interface IDoctorValidator
{
    /**
     * Valide les données d'inscription d'un médecin.
     * @param array $data Les données brutes du formulaire.
     * @return array Une liste d'erreurs (vide si tout est valide).
     */
    public function validateRegistration(array $data): array;
}
