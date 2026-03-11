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
        try {
            $this->checkAuth();
            $this->validateApiCsrf();

            $input = json_decode(file_get_contents('php://input'), true) ?? [];
            $formPatientId = $this->getPost('patient');
            if ($formPatientId === '') {
                $formPatientId = $this->getPost('ptId', '0');
            }
            // Support 'patient' (comme main) et 'ptId' (nouvelle architecture)
            $patientId = (int)($input['patient'] ?? $input['ptId'] ?? $formPatientId ?? 0);

            if ($patientId <= 0) {
                $this->json(['success' => false, 'error' => 'ID Patient invalide'], 400);
                return;
            }

            // Utiliser directement le script generate_data_online.php (comme dans main)
            require_once dirname(__DIR__, 3) . '/Scripts/generate_data_online.php';
            generatePatientData($patientId);

            $this->json([
                'success' => true,
                'message' => "Nouvelles mesures générées.",
                'timestamp' => date('H:i:s')
            ]);
        } catch (\Throwable $e) {
            error_log('[GENERATE_DATA] Erreur: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
