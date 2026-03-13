<?php

declare(strict_types=1);

namespace App\Controllers\Dashboard;

use Core\Controller\AbstractController;
use App\Models\Patient\Factories\PatientUseCaseFactory;
use App\Models\Patient\Repositories\PatientManagementRepository;
use App\Models\Patient\Repositories\PatientMonitoringRepository;
use App\Models\Patient\UseCases\Management\GetDoctorPatients;
use App\Models\Patient\UseCases\Monitoring\GetPatientChartData;

/**
 * Contrôleur principal du tableau de bord.
 *
 * Permet :
 * - De lister et d’afficher tous les patients du médecin connecté.
 * - D’afficher les mesures suivies pour un patient (données pour graphiques).
 */
final class DashboardController extends AbstractController
{
    private GetDoctorPatients $getPatientsUseCase;
    private GetPatientChartData $getChartsUseCase;

    /**
     * Prépare les usecases patients et graphiques.
     * Utilise la factory si disponible, sinon fallback via repository direct.
     */
    public function __construct()
    {
        if (class_exists(PatientUseCaseFactory::class)) {
            $this->getPatientsUseCase = PatientUseCaseFactory::createGetDoctorPatients();
            $this->getChartsUseCase = PatientUseCaseFactory::createGetPatientChartData();
            return;
        }
    }

    /**
     * Affiche la page principale du tableau de bord avec la liste des patients
     * et le suivi graphique du patient sélectionné.
     */
    public function index(): void
    {
        try {
            $this->checkAuth();
            $medId = $_SESSION['user']['id'] ?? 0;

            // Récupère les patients du médecin connecté via le use case
            $patientsObjects = $this->getPatientsUseCase->execute($medId);

            if (empty($patientsObjects)) {
                // Aucun patient n’est attribué à ce médecin
                $this->render('dashboard/dashboard', [
                    'noPatient' => true,
                    'patients' => [],
                    'patient' => null,
                    'chartData' => []
                ]);
                return;
            }

            // Extraction des patients sous forme de tableaux associatifs pour la vue
            $patientsArray = [];
            foreach ($patientsObjects as $patientObj) {
                $patientsArray[] = method_exists($patientObj, 'toArray')
                    ? $patientObj->toArray()
                    : (array)$patientObj;
            }

            // Détermine le patient sélectionné (demande, précédent ou premier)
            $currentPatientId = $this->resolveCurrentPatientId($patientsArray);

            $currentPatient = null;
            foreach ($patientsArray as $p) {
                if ((int)$p['pt_id'] === $currentPatientId) {
                    $currentPatient = $p;
                    break;
                }
            }

            // Définition stricte du mapping JS <-> base pour les métriques
            $metricsMap = [
                'temperature'       => 'Température corporelle',
                'blood-pressure'    => 'Tension artérielle',
                'heart-rate'        => 'Fréquence cardiaque',
                'respiration'       => 'Fréquence respiratoire',
                'glucose-trend'     => 'Glycémie',
                'weight'            => 'Poids',
                'oxygen-saturation' => 'Saturation en oxygène',
            ];

            // Récupère les mesures du patient sélectionné
            $chartData = [];
            foreach ($metricsMap as $jsKey => $dbName) {
                $data = $this->getChartsUseCase->execute($currentPatientId, $dbName);
                if (!empty($data)) {
                    $chartData[$jsKey] = $data;
                }
            }

            // Affiche la vue Dashboard avec toutes les infos nécessaires
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

    /**
     * Détermine le patient à sélectionner pour l’affichage :
     * - Si un ID valide est demandé parmi les patients, il est sélectionné et enregistré en session
     * - Sinon, si un précédent est mémorisé, on le reprend s’il est autorisé
     * - Sinon, on prend le premier de la liste
     *
     * @param array $patients Liste des patients (tableaux associatifs)
     * @return int ID du patient à afficher
     */
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