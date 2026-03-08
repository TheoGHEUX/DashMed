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
    private GetDoctorPatients $getPatients;
    private GetPatientChartData $getCharts;

    public function __construct()
    {
        $this->getPatients = new GetDoctorPatients(new PatientManagementRepository());
        $this->getCharts = new GetPatientChartData(new PatientMonitoringRepository());
    }

    public function index(): void
    {
        $this->checkAuth();
        $medId = $this->getCurrentUserId();

        // 1. Récupérer les patients
        $patientsObjects = $this->getPatients->execute($medId);

        if (empty($patientsObjects)) {
            $this->render('connected/dashboard', ['noPatient' => true]);
            return;
        }

        // 2. Transformer en tableau pour la vue
        $patientsArray = [];
        foreach ($patientsObjects as $p) {
            $patientsArray[] = $p->toArray();
        }

        // 3. Identifier le patient courant
        $patientId = $this->resolveCurrentPatientId($patientsArray);

        $currentPatient = null;
        foreach ($patientsArray as $p) {
            if ($p['pt_id'] === $patientId) {
                $currentPatient = $p;
                break;
            }
        }

        // 4. Récupérer les données graphiques
        $chartData = [];
        $metrics = ['Temperature', 'Tension', 'Frequence', 'Oxygene'];

        foreach ($metrics as $type) {
            if ($data = $this->getCharts->execute($patientId, $type)) {
                $chartData[$type] = $data;
            }
        }

        $this->render('connected/dashboard', [
            'patients' => $patientsArray,
            'patient' => $currentPatient,
            'chartData' => $chartData
        ]);
    }

    private function resolveCurrentPatientId(array $patients): int
    {
        $requested = (int)($_GET['patient'] ?? 0);
        $authorizedIds = array_column($patients, 'pt_id');

        if ($requested && in_array($requested, $authorizedIds, true)) {
            $_SESSION['last_patient_id'] = $requested;
            return $requested;
        }
        return $_SESSION['last_patient_id'] ?? $authorizedIds[0];
    }
}