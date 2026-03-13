<?php

declare(strict_types=1);

namespace App\Models\ConsoleLog\UseCases\Logging;

use App\Models\ConsoleLog\Interfaces\IActionLoggerRepository;

/**
 * Use case : loggue une action dans la console dashboard, après validation.
 */
final class LogDashboardAction
{
    private IActionLoggerRepository $repository;

    private const ACTION_MAP = [
        'ajouter'   => 0,
        'supprimer' => 1,
        'reduire'   => 2,
        'réduire'   => 2,
        'agrandir'  => 3,
        'deplacer'  => 4
    ];

    public function __construct(IActionLoggerRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Valide et log une action utilisateur sur le dashboard.
     *
     * - Valide l’action (connu du système)
     * - Récupère l’ID technique associé
     * - Appelle le repository pour persister la donnée
     */
    public function execute(int $medId, string $actionType, ?int $patientId = null, ?int $mesureId = null): bool
    {
        $lowerAction = strtolower(trim($actionType));

        if (!array_key_exists($lowerAction, self::ACTION_MAP)) {
            return false;
        }

        $actionId = self::ACTION_MAP[$lowerAction];

        return $this->repository->log(
            $medId,
            $lowerAction,
            $actionId,
            $patientId,
            $mesureId
        );
    }
}