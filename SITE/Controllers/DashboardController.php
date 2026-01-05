<?php

namespace Controllers;

use Models\Patient;
use Models\HistoriqueConsole;

/**
 * Contrôleur : Tableau de bord
 *
 * Prépare les données patients et les séries pour les graphiques du dashboard.
 * Méthode principale :
 *  - index() : récupère patient + séries et affiche la vue ../Views/dashboard.php
 *
 * @package Controllers
 */
final class DashboardController
{
    /**
     * Affiche la page du tableau de bord avec graphiques et infos patients.
     *
     * @return void
     */
    public function index(): void
    {
        if (empty($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        $medId = (int) $_SESSION['user']['id'];

        // 🧠 1. Si patient dans l'URL → sauvegarde
        if (isset($_GET['patient']) && ctype_digit($_GET['patient'])) {
            $_SESSION['last_patient_id'] = (int) $_GET['patient'];
        }

        // 🧠 2. Patient actif
        $patientId = $_SESSION['last_patient_id'] ?? null;

        // 🧠 3. Fallback (premier patient du médecin)
        if (!$patientId) {
            $patientId = Patient::getFirstPatientIdForDoctor($_SESSION['user']['id']);
            $_SESSION['last_patient_id'] = $patientId;
        }

        if (!$patientId) {
            http_response_code(404);
            \Core\View::render('errors/404');
            return;
        }

        // Vérifier que le patient est bien rattaché au médecin connecté
        $patient = Patient::findByIdForDoctor($patientId, $medId);
        if (!$patient) {
            http_response_code(403);
            \Core\View::render('errors/404');
            return;
        }

        // Récupérer les données pour chaque type de graphique
        $chartData = [];

        // Température corporelle (35-40°C)
        $tempData = Patient::getChartDataForDoctor($medId, $patientId, 'Température corporelle', 50);
        if ($tempData) {
            $chartData['temperature'] = [
                'values' => Patient::prepareChartValues($tempData['valeurs'], 35.0, 40.0),
                'lastValue' => end($tempData['valeurs'])['valeur'],
                'unit' => $tempData['unite']
            ];
        }

        // Tension artérielle (100-140 mmHg)
        $tensionData = Patient::getChartDataForDoctor($medId, $patientId, 'Tension arterielle', 50);
        if (!$tensionData) {
            $tensionData = Patient::getChartDataForDoctor($medId, $patientId, 'Tension artérielle', 50);
        }
        if ($tensionData) {
            $chartData['blood-pressure'] = [
                'values' => Patient::prepareChartValues($tensionData['valeurs'], 100, 140),
                'lastValue' => end($tensionData['valeurs'])['valeur'],
                'unit' => $tensionData['unite']
            ];
        }

        // Fréquence cardiaque (60-100 bpm)
        $fcData = Patient::getChartDataForDoctor($medId, $patientId, 'Fréquence cardiaque', 50);
        if ($fcData) {
            $chartData['heart-rate'] = [
                'values' => Patient::prepareChartValues($fcData['valeurs'], 25, 100),
                'lastValue' => end($fcData['valeurs'])['valeur'],
                'unit' => $fcData['unite']
            ];
        }

        // Fréquence respiratoire (12-20 resp/min)
        $respData = Patient::getChartDataForDoctor($medId, $patientId, 'Fréquence respiratoire', 50);
        if ($respData) {
            $chartData['respiration'] = [
                'values' => Patient::prepareChartValues($respData['valeurs'], 0, 20),
                'lastValue' => end($respData['valeurs'])['valeur'],
                'unit' => $respData['unite']
            ];
        }

        // Glycémie (4.0-7.5 mmol/L)
        $glycemieData = Patient::getChartDataForDoctor($medId, $patientId, 'Glycémie', 50);
        if ($glycemieData) {
            $chartData['glucose-trend'] = [
                'values' => Patient::prepareChartValues($glycemieData['valeurs'], 4.0, 7.5),
                'lastValue' => end($glycemieData['valeurs'])['valeur'],
                'unit' => $glycemieData['unite']
            ];
        }

        // Poids (35-110 kg)
        $poidsData = Patient::getChartDataForDoctor($medId, $patientId, 'Poids', 50);
        if ($poidsData) {
            $chartData['weight'] = [
                'values' => Patient::prepareChartValues($poidsData['valeurs'], 35, 110),
                'lastValue' => end($poidsData['valeurs'])['valeur'],
                'unit' => $poidsData['unite']
            ];
        }

        // Saturation en oxygène (95-100%)
        $o2Data = Patient::getChartDataForDoctor($medId, $patientId, 'Saturation en oxygène', 50);
        if ($o2Data) {
            $chartData['oxygen-saturation'] = [
                'values' => Patient::prepareChartValues($o2Data['valeurs'], 90, 100),
                'lastValue' => end($o2Data['valeurs'])['valeur'],
                'unit' => $o2Data['unite']
            ];
        }

        // Récupérer la liste complète des patients du médecin pour la liste déroulante
        $patients = Patient::getPatientsForDoctor($medId);

        require __DIR__ . '/../Views/dashboard.php';
    }

    /**
     * API endpoint pour logger les actions sur les graphiques
     * Attend POST avec JSON: {"action": "ouvrir"|"réduire"}
     *
     * @return void
     */
    public function logGraphAction(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (empty($_SESSION['user'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Non authentifié']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Méthode non autorisée']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $action = $input['action'] ?? null;

        if (!$action || !in_array($action, ['ouvrir', 'réduire'], true)) {
            http_response_code(400);
            error_log(sprintf('[LOG] Action invalide reçue: %s', var_export($input, true)));
            echo json_encode(['error' => 'Action invalide']);
            exit;
        }

        $medId = (int) $_SESSION['user']['id'];
        
        try {
            $historiqueConsole = new HistoriqueConsole();
            
            if ($action === 'ouvrir') {
                $success = $historiqueConsole->logGraphiqueOuvrir($medId);
            } else {
                $success = $historiqueConsole->logGraphiqueReduire($medId);
            }
            
            if (!$success) {
                error_log(sprintf('[LOG] Échec du log: med_id=%d, action=%s', $medId, $action));
                http_response_code(500);
                echo json_encode(['error' => 'Échec de l\'enregistrement']);
                exit;
            }
            
            echo json_encode(['success' => true, 'action' => $action]);
        } catch (\Exception $e) {
            error_log(sprintf('[LOG] Exception: %s | Trace: %s', $e->getMessage(), $e->getTraceAsString()));
            http_response_code(500);
            echo json_encode(['error' => 'Erreur serveur', 'message' => $e->getMessage()]);
        }
        exit;
    }
}
