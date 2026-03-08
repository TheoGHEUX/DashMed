<?php

declare(strict_types=1);

namespace Models\ConsoleLog\Interfaces;

interface IActionLoggerRepository
{
    public function log(int $medId, string $typeAction, int $typeActionId, ?int $ptId = null, ?int $idMesure = null): bool;
}