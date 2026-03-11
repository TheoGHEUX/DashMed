<?php

declare(strict_types=1);

namespace App\Controllers\Dashboard;

use Core\Controller\AbstractController;
use App\Models\Patient\Repositories\PatientMonitoringRepository;
use App\Models\Patient\UseCases\Monitoring\GetPatientChartData;

final class ChartApiController extends AbstractController
{
    private GetPatientChartData $useCase;
    private PatientMonitoringRepository $repo; // Utile si generateData a besoin du repo direct

    public function __construct()
    {
        $this->repo = new PatientMonitoringRepository();
        $this->useCase = new GetPatientChartData($this->repo);
    }

    /**
     * LECTURE : Récupère les données pour les graphiques (GET)
     * Compatible avec l'ancien endpoint /api/dashboard/chart-data
     */
    public function getData(): void
    {
        $this->checkAuth();

        $patientId = (int)($_GET['ptId'] ?? 0);
        if ($patientId <= 0) {
            $this->json(['success' => false, 'error' => 'ID Patient invalide'], 400);
            return;
        }

        // Configuration des métriques (comme dans le main)
        $metrics = [
            'temperature'       => 'Température corporelle',
            'blood-pressure'    => 'Tension artérielle',
            'heart-rate'        => 'Fréquence cardiaque',
            'respiration'       => 'Fréquence respiratoire',
            'glucose-trend'     => 'Glycémie',
            'weight'            => 'Poids',
            'oxygen-saturation' => 'Saturation en oxygène'
        ];

        $results = [];
        foreach ($metrics as $jsKey => $dbLabel) {
            $data = $this->useCase->execute($patientId, $dbLabel);
            if ($data) $results[$jsKey] = $data;
        }

        $this->json(['success' => true, 'chartData' => $results]);
    }

    /**
     * ECRITURE : Génère des données pour tester (POST)
     */

    /**
     * ECRITURE : Simule de nouvelles données (basé sur generate_data_online.php)
     * Route : POST /generate-data
     */
    public function generateData(): void
    {
        $this->checkAuth();
        $this->validateApiCsrf();

        $input = $this->getJsonInput();
        $patientId = (int)($input['ptId'] ?? $_POST['ptId'] ?? 0);

        if ($patientId <= 0) {
            $this->json(['success' => false, 'error' => 'ID Patient invalide'], 400);
            return;
        }

        // On délègue la logique complexe au Repository
        $count = $this->repo->generateSimulationData($patientId);

        $this->json([
            'success' => true,
            'message' => "$count nouvelles mesures générées.",
            'timestamp' => date('H:i:s')
        ]);
    }
}