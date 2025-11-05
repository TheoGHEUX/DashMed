<?php
namespace Controllers;

use Models\Patient;

/**
 * Contrôleur pour le vrai dashboard avec graphiques et statistiques
 * 
*/
final class DashboardController {
    /**
     * Affiche la page du tableau de bord avec graphiques et infos patients
     * 
     * @return void
     */
    public function index(): void {
        if (empty($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        // Pour l'instant, on utilise le patient 1 (Alexandre Jacob)
        // Plus tard, on récupérera les patients liés au médecin connecté
        $patientId = 1;
        
        // Récupérer les informations du patient
        $patient = Patient::findById($patientId);
        
        // Récupérer les données pour chaque type de graphique
        $chartData = [];
        
        // Température corporelle (35-40°C)
        $tempData = Patient::getChartData($patientId, 'Température corporelle', 50);
        if ($tempData) {
            $chartData['temperature'] = [
                'values' => Patient::prepareChartValues($tempData['valeurs'], 35.0, 40.0),
                'lastValue' => end($tempData['valeurs'])['valeur'],
                'unit' => $tempData['unite']
            ];
        }
        
        // Tension artérielle (100-140 mmHg)
        $tensionData = Patient::getChartData($patientId, 'Tension arterielle', 50);
        if (!$tensionData) {
            $tensionData = Patient::getChartData($patientId, 'Tension artérielle', 50);
        }
        if ($tensionData) {
            $chartData['blood-pressure'] = [
                'values' => Patient::prepareChartValues($tensionData['valeurs'], 100, 140),
                'lastValue' => end($tensionData['valeurs'])['valeur'],
                'unit' => $tensionData['unite']
            ];
        }
        
        // Fréquence cardiaque (60-100 bpm)
        $fcData = Patient::getChartData($patientId, 'Fréquence cardiaque', 50);
        if ($fcData) {
            $chartData['heart-rate'] = [
                'values' => Patient::prepareChartValues($fcData['valeurs'], 60, 100),
                'lastValue' => end($fcData['valeurs'])['valeur'],
                'unit' => $fcData['unite']
            ];
        }
        
        // Fréquence respiratoire (12-20 resp/min)
        $respData = Patient::getChartData($patientId, 'Fréquence respiratoire', 50);
        if ($respData) {
            $chartData['respiration'] = [
                'values' => Patient::prepareChartValues($respData['valeurs'], 12, 20),
                'lastValue' => end($respData['valeurs'])['valeur'],
                'unit' => $respData['unite']
            ];
        }
        
        // Glycémie (4.0-7.5 mmol/L)
        $glycemieData = Patient::getChartData($patientId, 'Glycémie', 50);
        if ($glycemieData) {
            $chartData['glucose-trend'] = [
                'values' => Patient::prepareChartValues($glycemieData['valeurs'], 4.0, 7.5),
                'lastValue' => end($glycemieData['valeurs'])['valeur'],
                'unit' => $glycemieData['unite']
            ];
        }
        
        // Poids (35-110 kg)
        $poidsData = Patient::getChartData($patientId, 'Poids', 50);
        if ($poidsData) {
            $chartData['weight'] = [
                'values' => Patient::prepareChartValues($poidsData['valeurs'], 35, 110),
                'lastValue' => end($poidsData['valeurs'])['valeur'],
                'unit' => $poidsData['unite']
            ];
        }
        
        // Saturation en oxygène (95-100%)
        $o2Data = Patient::getChartData($patientId, 'Saturation en oxygène', 50);
        if ($o2Data) {
            $chartData['oxygen-saturation'] = [
                'values' => Patient::prepareChartValues($o2Data['valeurs'], 95, 100),
                'lastValue' => end($o2Data['valeurs'])['valeur'],
                'unit' => $o2Data['unite']
            ];
        }

        require __DIR__ . '/../Views/dashboard.php';
    }
}
