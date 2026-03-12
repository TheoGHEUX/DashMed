<?php

declare(strict_types=1);

namespace App\Models\ConsoleLog\UseCases\Logging;

use App\Models\ConsoleLog\Interfaces\IActionLoggerRepository;

/**
 * Use case pour enregistrer une action utilisateur sur le dashboard.
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
     * Exécute la logique de log : Valide l'action, trouve son ID, et persiste.
     */
    public function execute(int $medId, string $actionType, ?int $patientId = null, ?int $mesureId = null): bool
    {
        // 1. Normalisation (minuscule + trim)
        $lowerAction = strtolower(trim($actionType));

        // 2. Validation : Est-ce une action connue ?
        if (!array_key_exists($lowerAction, self::ACTION_MAP)) {
            // Action inconnue : on ne loggue pas
            return false;
        }

        // 3. Récupération de l'ID technique
        $actionId = self::ACTION_MAP[$lowerAction];

        // 4. Appel au Repository 
        return $this->repository->log(
            $medId,        
            $lowerAction,  
            $actionId,     
            $patientId,    
            $mesureId      
        );
    }
}
