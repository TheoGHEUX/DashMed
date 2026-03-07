<?php

declare(strict_types=1);

namespace Models\ConsoleLog\Interfaces;

use Models\ConsoleLog\Entities\ConsoleLog;

interface ILogHistoryRepository
{
    /** @return ConsoleLog[] */
    public function getHistoryByMedId(int $medId, int $limit = 100): array;
}