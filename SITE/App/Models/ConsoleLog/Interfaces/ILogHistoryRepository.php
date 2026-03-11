<?php

declare(strict_types=1);

namespace App\Models\ConsoleLog\Interfaces;

use App\Models\ConsoleLog\Entities\ConsoleLog;

interface ILogHistoryRepository
{
    /** @return ConsoleLog[] */
    public function getHistoryByMedId(int $medId, int $limit = 100): array;
}
