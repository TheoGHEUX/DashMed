<?php

declare(strict_types=1);

namespace App\Models\ConsoleLog\Interfaces;

/**
 * Contrat pour l’enregistrement d’une action utilisateur dans le log console.
 */
interface IActionLoggerRepository
{
    /**
     * Enregistre une action utilisateur sur le dashboard.
     *
     * @param int $medId        ID du médecin
     * @param string $typeAction    Type de l’action (ex: clic, ajout, suppression…)
     * @param int $typeActionId     ID précis de l’action
     * @param int|null $ptId        (Optionnel) ID du patient concerné
     * @param int|null $idMesure    (Optionnel) ID de la mesure concernée
     * @return bool                 true si succès, false sinon
     */
    public function log(int $medId, string $typeAction, int $typeActionId, ?int $ptId = null, ?int $idMesure = null): bool;
}