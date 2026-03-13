<?php

declare(strict_types=1);

namespace App\Models\Patient\Factories;

use App\Models\Patient\Repositories\DashboardLayoutRepository;
use App\Models\Patient\Repositories\PatientManagementRepository;
use App\Models\Patient\Repositories\PatientMonitoringRepository;
use App\Models\Patient\UseCases\Dashboard\GetDashboardLayout;
use App\Models\Patient\UseCases\Dashboard\PatientSimilarityService;
use App\Models\Patient\UseCases\Dashboard\SaveDashboardLayout;
use App\Models\Patient\UseCases\Dashboard\SuggestLayout;
use App\Models\Patient\UseCases\Management\GetDoctorPatients;
use App\Models\Patient\UseCases\Monitoring\GetPatientChartData;

/**
 * Factory centralisée pour obtenir les Use Cases liés aux patients.
 *
 * Singleton pour chaque repository/service pour assurer cohérence et éviter les multiples connexions.
 */
final class PatientUseCaseFactory
{
    private static ?PatientManagementRepository $managementRepo = null;
    private static ?PatientMonitoringRepository $monitoringRepo = null;
    private static ?DashboardLayoutRepository $layoutRepo = null;
    private static ?PatientSimilarityService $similarityService = null;

    private static function getManagementRepo(): PatientManagementRepository
    {
        if (self::$managementRepo === null) {
            self::$managementRepo = new PatientManagementRepository();
        }
        return self::$managementRepo;
    }

    private static function getMonitoringRepo(): PatientMonitoringRepository
    {
        if (self::$monitoringRepo === null) {
            self::$monitoringRepo = new PatientMonitoringRepository();
        }
        return self::$monitoringRepo;
    }

    private static function getLayoutRepo(): DashboardLayoutRepository
    {
        if (self::$layoutRepo === null) {
            self::$layoutRepo = new DashboardLayoutRepository();
        }
        return self::$layoutRepo;
    }

    private static function getSimilarityService(): PatientSimilarityService
    {
        if (self::$similarityService === null) {
            self::$similarityService = new PatientSimilarityService();
        }
        return self::$similarityService;
    }

    // Use Cases

    /** Récupère tous les patients d’un médecin. */
    public static function createGetDoctorPatients(): GetDoctorPatients
    {
        return new GetDoctorPatients(self::getManagementRepo());
    }

    /** Récupère les séries de mesure pour un patient. */
    public static function createGetPatientChartData(): GetPatientChartData
    {
        return new GetPatientChartData(self::getMonitoringRepo());
    }

    /** Récupère le layout dashboard d’un patient pour un médecin. */
    public static function createGetDashboardLayout(): GetDashboardLayout
    {
        return new GetDashboardLayout(self::getLayoutRepo());
    }

    /** Sauvegarde le layout dashboard d’un patient pour un médecin. */
    public static function createSaveDashboardLayout(): SaveDashboardLayout
    {
        return new SaveDashboardLayout(self::getLayoutRepo());
    }

    /** Suggère automatiquement un layout à partir de patients similaires. */
    public static function createSuggestLayout(): SuggestLayout
    {
        return new SuggestLayout(self::getLayoutRepo(), self::getSimilarityService());
    }
}