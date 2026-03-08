<?php

declare(strict_types=1);

namespace App\Controllers\Dashboard;

use Core\Controller\AbstractController;
use App\Models\Patient\Repositories\PatientManagementRepository;
use App\Models\Patient\Repositories\PatientMonitoringRepository;
use App\Models\Patient\UseCases\Management\GetDoctorPatients;
use App\Models\Patient\UseCases\Monitoring\GetPatientChartData;

final class DashboardController extends AbstractController
{
    private GetDoctorPatients $getPatientsUseCase;
    private GetPatientChartData $getChartsUseCase;

    public function __construct()
    {
        $managementRepo = new PatientManagementRepository();
        $monitoringRepo = new PatientMonitoringRepository();
        $this->getPatientsUseCase = new GetDoctorPatients($managementRepo);
        $this->getChartsUseCase = new GetPatientChartData($monitoringRepo);
    }

    public function index(): void
    {
        $this->checkAuth();
        $medId = $_SESSION['user']['id'] ?? 0;

        // 1. Récupération des patients
        $patientsObjects = $this->getPatientsUseCase->execute($medId);

        if (empty($patientsObjects)) {
            $this->render('Dashboard/dashboard', [
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

        // 2. Récupérer les données graphiques (CORRECTION ICI)
        $chartData = [];

        // Mapping : Clé JS => Nom en Base de Données
        // C'est CRUCIAL pour que le JS trouve les données au chargement
        $metricsMap = [
            'temperature'       => 'Temperature',
            'blood-pressure'    => 'Tension',
            'heart-rate'        => 'Frequence_Cardiaque',
            'respiration'       => 'Frequence_Respiratoire', // Vérifie ce nom en BDD !
            'glucose-trend'     => 'Glycemie',
            'weight'            => 'Poids',
            'oxygen-saturation' => 'Oxygene'
        ];

        foreach ($metricsMap as $jsKey => $dbName) {
            $data = $this->getChartsUseCase->execute($currentPatientId, $dbName);
            if (!empty($data)) {
                $chartData[$jsKey] = $data; // On utilise la clé JS ici
            }
        }

        $this->render('Dashboard/dashboard', [
            'noPatient' => false,
            'patients' => $patientsArray,
            'patient' => $currentPatient,
            'chartData' => $chartData
        ]);
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