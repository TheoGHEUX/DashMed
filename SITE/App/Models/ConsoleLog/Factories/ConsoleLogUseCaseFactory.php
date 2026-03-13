<?php

declare(strict_types=1);

namespace App\Models\ConsoleLog\Factories;

use App\Models\ConsoleLog\Repositories\ActionLoggerRepository;
use App\Models\ConsoleLog\Services\TreePredictor;
use App\Models\ConsoleLog\UseCases\Intelligence\PredictNextAction;
use App\Models\ConsoleLog\UseCases\Logging\LogDashboardAction;

/**
 * Factory centralisant la création des use cases liés au log console.
 *
 * Cette classe permet d’instancier facilement les loggers,
 * prédicteurs, etc, utilisé par les couches supérieures.
 */
final class ConsoleLogUseCaseFactory
{
    private static ?ActionLoggerRepository $loggerRepo = null;
    private static ?TreePredictor $predictor = null;

    /**
     * Accès interne (singleton) au repository logger d’actions.
     */
    private static function getLoggerRepo(): ActionLoggerRepository
    {
        if (self::$loggerRepo === null) {
            self::$loggerRepo = new ActionLoggerRepository();
        }
        return self::$loggerRepo;
    }

    /**
     * Accès interne (singleton) au service de prédiction.
     */
    private static function getPredictor(): TreePredictor
    {
        if (self::$predictor === null) {
            self::$predictor = new TreePredictor();
        }
        return self::$predictor;
    }

    /**
     * Crée un use case pour les logs du dashboard.
     * @return LogDashboardAction
     */
    public static function createLogDashboardAction(): LogDashboardAction
    {
        return new LogDashboardAction(self::getLoggerRepo());
    }

    /**
     * Crée un use case pour la prédiction de prochaine action.
     * @return PredictNextAction
     */
    public static function createPredictNextAction(): PredictNextAction
    {
        return new PredictNextAction(self::getPredictor());
    }
}