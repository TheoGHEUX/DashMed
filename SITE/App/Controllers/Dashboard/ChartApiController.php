<?php

declare(strict_types=1);

namespace App\Controllers\Dashboard;

use Core\Controller\AbstractController;
use App\Models\Patient\Factories\PatientUseCaseFactory;
use App\Models\Patient\Repositories\PatientMonitoringRepository;
use App\Models\Patient\UseCases\Monitoring\GetPatientChartData;

/**
 * Contrôleur API lié aux graphiques de suivi patient (Dashboard).
 *
 * - Permet de récupérer les données pour alimenter les graphiques du dashboard d’un patient.
 * - Offre aussi un endpoint pour générer des données de test (pour la démo ou le dev).
 */
final class ChartApiController extends AbstractController
{
    private GetPatientChartData $useCase;

    /**
     * Prépare le use case pour la récupération des données du patient.
     * Utilise la factory si disponible, sinon un fallback avec repository direct.
     */
    public function __construct()
    {
        if (class_exists(PatientUseCaseFactory::class)) {
            $this->useCase = PatientUseCaseFactory::createGetPatientChartData();
            return;
        }

    }

    /**
     * GET: Récupère les données de santé du patient pour affichage graphique.
     */
    public function getData(): void
    {
        $this->checkAuth();

        // Récupère et contrôle l’identifiant du patient depuis la requête
        $patientId = (int)($_GET['ptId'] ?? 0);
        if ($patientId <= 0) {
            $this->json(['success' => false, 'error' => 'ID Patient invalide'], 400);
            return;
        }

        // Définition des métriques disponibles pour les graphiques
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
        // Récupère les séries de données une par une via le use case
        foreach ($metrics as $jsKey => $dbLabel) {
            $data = $this->useCase->execute($patientId, $dbLabel);
            if ($data) {
                $results[$jsKey] = $data;
            }
        }

        $this->json(['success' => true, 'chartData' => $results]);
    }

    /**
     * POST: Génère et insère des données pour un patient (sandbox/dev).
     *
     * Utilise en interne le script generate_data_online.php, accepte l’id patient en POST ou dans le JSON d’entrée.
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

            // Génère des données fictives par appel du script existant
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