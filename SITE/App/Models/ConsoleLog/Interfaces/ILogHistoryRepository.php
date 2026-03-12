<?php

declare(strict_types=1);

namespace App\Models\ConsoleLog\Interfaces;

use App\Models\ConsoleLog\Entities\ConsoleLog;

/**
 * Interface pour les repositories d'historique d'actions du dashboard.
 */
interface ILogHistoryRepository
{
    /**
     * Retourne l'historique des actions pour un médecin.
     * @return ConsoleLog[]
     */
    public function getHistoryByMedId(int $medId, int $limit = 100): array;
}
