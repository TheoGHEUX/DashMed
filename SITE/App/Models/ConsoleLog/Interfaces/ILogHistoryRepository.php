<?php

declare(strict_types=1);

namespace App\Models\ConsoleLog\Interfaces;

use App\Models\ConsoleLog\Entities\ConsoleLog;

/**
 * Contrat pour la récupération de l’historique des logs d’un médecin.
 */
interface ILogHistoryRepository
{
    /**
     * Retourne l’historique des logs du médecin sous forme d’objets ConsoleLog.
     *
     * @param int $medId ID du médecin concerné
     * @param int $limit Limite de résultats (par défaut : 100)
     * @return ConsoleLog[] Liste des logs relevés
     */
    public function getHistoryByMedId(int $medId, int $limit = 100): array;
}