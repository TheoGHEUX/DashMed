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

    public static function createGetDoctorPatients(): GetDoctorPatients
    {
        return new GetDoctorPatients(self::getManagementRepo());
    }

    public static function createGetPatientChartData(): GetPatientChartData
    {
        return new GetPatientChartData(self::getMonitoringRepo());
    }

    public static function createGetDashboardLayout(): GetDashboardLayout
    {
        return new GetDashboardLayout(self::getLayoutRepo());
    }

    public static function createSaveDashboardLayout(): SaveDashboardLayout
    {
        return new SaveDashboardLayout(self::getLayoutRepo());
    }

    public static function createSuggestLayout(): SuggestLayout
    {
        return new SuggestLayout(self::getLayoutRepo(), self::getSimilarityService());
    }
}
