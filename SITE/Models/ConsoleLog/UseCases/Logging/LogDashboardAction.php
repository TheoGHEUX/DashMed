<?php

declare(strict_types=1);

namespace Models\ConsoleLog\UseCases\Logging;

use Models\ConsoleLog\Interfaces\IActionLoggerRepository;

class LogDashboardAction
{
    private IActionLoggerRepository $repository;

    // Mapping des actions vers leurs IDs (Logique Métier)
    private const ACTION_MAP = [
        'ajouter'   => 0,
        'supprimer' => 1,
        'réduire'   => 2,
        'agrandir'  => 3,
    ];

    public function __construct(IActionLoggerRepository $repository)
    {
        $this->repository = $repository;
    }

    public function execute(int $medId, string $actionType, ?int $patientId = null, ?int $mesureId = null): bool
    {
        // 1. Validation de l'action
        $lowerAction = strtolower($actionType);
        if (!array_key_exists($lowerAction, self::ACTION_MAP)) {
            // On pourrait lancer une exception ici, ou juste retourner false
            return false;
        }

        // 2. Récupération de l'ID technique correspondant
        $actionId = self::ACTION_MAP[$lowerAction];

        // 3. Appel du Repository (qui ne fait que l'insertion SQL)
        return $this->repository->log($medId, $lowerAction, $actionId, $patientId, $mesureId);
    }
}