<?php

declare(strict_types=1);

namespace App\Controllers\Dashboard;

use Core\Controller\AbstractController;
use App\Models\Patient\Factories\PatientUseCaseFactory;
use App\Models\Patient\UseCases\Management\GetDoctorPatients;
use App\Models\Patient\UseCases\Monitoring\GetPatientChartData;

final class DashboardController extends AbstractController
{
    private GetDoctorPatients $getPatientsUseCase;
    private GetPatientChartData $getChartsUseCase;

    public function __construct()
    {
        $this->getPatientsUseCase = PatientUseCaseFactory::createGetDoctorPatients();
        $this->getChartsUseCase = PatientUseCaseFactory::createGetPatientChartData();
    }

    public function index(): void
    {
        try {
            $this->checkAuth();
            $medId = $_SESSION['user']['id'] ?? 0;

            // 1. Récupération des patients
            $patientsObjects = $this->getPatientsUseCase->execute($medId);

            if (empty($patientsObjects)) {
                $this->render('dashboard/dashboard', [
                    'noPatient' => true,
                    'patients' => [],
                    'patient' => null,
                    'chartData' => []
                ]);
                return;
            }

            $patientsArray = [];
            foreach ($patientsObjects as $patientObj) {
                $patientsArray[] = method_exists($patientObj, 'toArray')
                    ? $patientObj->toArray()
                    : (array)$patientObj;
            }

            $currentPatientId = $this->resolveCurrentPatientId($patientsArray);

            $currentPatient = null;
            foreach ($patientsArray as $p) {
                if ((int)$p['pt_id'] === $currentPatientId) {
                    $currentPatient = $p;
                    break;
                }
            }

            // MAPPING STRICT : clé JS => nom EXACTEMENT comme en BDD
            $metricsMap = [
                'temperature'       => 'Température corporelle',
                'blood-pressure'    => 'Tension artérielle',
                'heart-rate'        => 'Fréquence cardiaque',
                'respiration'       => 'Fréquence respiratoire',
                'glucose-trend'     => 'Glycémie',
                'weight'            => 'Poids',
                'oxygen-saturation' => 'Saturation en oxygène',
            ];

            $chartData = [];
            foreach ($metricsMap as $jsKey => $dbName) {
                $data = $this->getChartsUseCase->execute($currentPatientId, $dbName);
                if (!empty($data)) {
                    $chartData[$jsKey] = $data;
                }
            }

            $this->render('dashboard/dashboard', [
                'noPatient' => false,
                'patients' => $patientsArray,
                'patient' => $currentPatient,
                'chartData' => $chartData
            ]);
        } catch (\Throwable $e) {
            error_log('[DASHBOARD] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            $this->render('dashboard/dashboard', [
                'noPatient' => true,
                'patients' => [],
                'patient' => null,
                'chartData' => []
            ]);
        }
    }

    private function resolveCurrentPatientId(array $patients): int
    {
        $requestedId = (int)($_GET['patient'] ?? 0);
        $authorizedIds = array_column($patients, 'pt_id');

        if ($requestedId > 0 && in_array($requestedId, $authorizedIds, true)) {
            $_SESSION['last_patient_id'] = $requestedId;
            return $requestedId;
        }

        $lastId = $_SESSION['last_patient_id'] ?? 0;
        if ($lastId > 0 && in_array($lastId, $authorizedIds, true)) {
            return $lastId;
        }

        return (int)($authorizedIds[0] ?? 0);
    }
}
