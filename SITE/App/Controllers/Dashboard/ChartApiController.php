<?php

declare(strict_types=1);

namespace App\Controllers\Dashboard;

use Core\Controller\AbstractController;
use App\Models\Patient\Factories\PatientUseCaseFactory;
use App\Models\Patient\Repositories\PatientMonitoringRepository;
use App\Models\Patient\UseCases\Monitoring\GetPatientChartData;

final class ChartApiController extends AbstractController
{
    private GetPatientChartData $useCase;
/**
 * API pour les données de graphiques du dashboard patient.
 *
 * Fournit les données nécessaires à l'affichage des courbes et indicateurs..
 */

    public function __construct()
    {
        if (class_exists(PatientUseCaseFactory::class)) {
            $this->useCase = PatientUseCaseFactory::createGetPatientChartData();
            return;
        }
        $repo = new PatientMonitoringRepository();
        $this->useCase = new GetPatientChartData($repo);
    }

    /**
     * LECTURE : Récupère les données pour les graphiques (GET)
     */
    public function getData(): void
    {
        $this->checkAuth();

        $patientId = (int)($_GET['ptId'] ?? 0);
        if ($patientId <= 0) {
            $this->json(['success' => false, 'error' => 'ID Patient invalide'], 400);
            return;
        }

        // Configuration des métriques
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
            if ($data) {
                $results[$jsKey] = $data;
            }
        }

        $this->json(['success' => true, 'chartData' => $results]);
    }

    /**
     * ECRITURE : Génère des données pour tester (POST)
     */

    /**
     * ECRITURE : Simule de nouvelles données
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
            $patientId = (int)($input['patient'] ?? $input['ptId'] ?? $formPatientId);

            if ($patientId <= 0) {
                $this->json(['success' => false, 'error' => 'ID Patient invalide'], 400);
                return;
            }

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
