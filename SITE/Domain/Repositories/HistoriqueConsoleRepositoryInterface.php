<?php

namespace Domain\Repositories;

interface HistoriqueConsoleRepositoryInterface
{
    public function log(int $medId, string $typeAction, ?int $ptId = null, ?int $idMesure = null): bool;

    public function getHistoryByMedId(int $medId, int $limit = 100): array;
}
