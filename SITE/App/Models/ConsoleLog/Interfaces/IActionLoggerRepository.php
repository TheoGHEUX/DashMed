<?php

declare(strict_types=1);

namespace App\Models\ConsoleLog\Interfaces;

/**
 * Interface pour les repositories d'enregistrement d'actions utilisateur.
 */
interface IActionLoggerRepository
{
    /**
     * Enregistre une action utilisateur dans l'historique.
     */
    public function log(int $medId, string $typeAction, int $typeActionId, ?int $ptId = null, ?int $idMesure = null): bool;
}
