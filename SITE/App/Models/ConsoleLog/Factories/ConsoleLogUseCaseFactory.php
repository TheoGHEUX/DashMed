<?php

declare(strict_types=1);

namespace App\Models\ConsoleLog\Factories;

use App\Models\ConsoleLog\Repositories\ActionLoggerRepository;
use App\Models\ConsoleLog\Services\TreePredictor;
use App\Models\ConsoleLog\UseCases\Intelligence\PredictNextAction;
use App\Models\ConsoleLog\UseCases\Logging\LogDashboardAction;

final class ConsoleLogUseCaseFactory
{
    private static ?ActionLoggerRepository $loggerRepo = null;
    private static ?TreePredictor $predictor = null;

    private static function getLoggerRepo(): ActionLoggerRepository
    {
        if (self::$loggerRepo === null) {
            self::$loggerRepo = new ActionLoggerRepository();
        }
        return self::$loggerRepo;
    }

    private static function getPredictor(): TreePredictor
    {
        if (self::$predictor === null) {
            self::$predictor = new TreePredictor();
        }
        return self::$predictor;
    }

    public static function createLogDashboardAction(): LogDashboardAction
    {
        return new LogDashboardAction(self::getLoggerRepo());
    }

    public static function createPredictNextAction(): PredictNextAction
    {
        return new PredictNextAction(self::getPredictor());
    }
}
