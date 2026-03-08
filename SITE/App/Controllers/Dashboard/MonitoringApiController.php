<?php

declare(strict_types=1);

namespace App\Controllers\Dashboard; //TODO : à revoir, peut-être mettre dans un namespace API ? (App\Controllers\API\MonitoringController) c'est bizzare de mélanger API et Dashboard, à voir selon l'évolution du projet

use Core\Controller\AbstractController;
use Models\Patient\Repositories\PatientMonitoringRepository;
use Models\Patient\UseCases\Monitoring\GetPatientChartData;

final class MonitoringApiController extends AbstractController
{
    private GetPatientChartData $useCase;

    public function __construct()
    {
        // Injection : Repo -> UseCase
        $this->useCase = new GetPatientChartData(new PatientMonitoringRepository());
    }

    public function getCharts(): void
    {
        // Vérif Auth API (Session + JSON header)
        if (empty($_SESSION['user'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Non authentifié']);
            exit;
        }
        header('Content-Type: application/json');

        $patientId = (int)($_GET['ptId'] ?? 0);

        // Liste des métriques à récupérer (comme avant)
        $metrics = [
            'temperature' => 'Température corporelle',
            'blood-pressure' => 'Tension artérielle',
            'heart-rate' => 'Fréquence cardiaque',
            'respiration' => 'Fréquence respiratoire',
            'glucose-trend' => 'Glycémie',
            'weight' => 'Poids',
            'oxygen-saturation' => 'Saturation en oxygène'
        ];

        $results = [];
        foreach ($metrics as $key => $label) {
            // Appel du UseCase que tu m'as montré tout à l'heure
            $data = $this->useCase->execute($patientId, $label);
            if ($data) {
                $results[$key] = $data;
            }
        }

        echo json_encode(['success' => true, 'chartData' => $results]);
        exit;
    }
}