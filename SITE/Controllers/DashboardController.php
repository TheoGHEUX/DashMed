<?php

namespace Controllers;

use Models\Patient;

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
        // Vérification de l'authentification
        if (empty($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        // Patients suivis par le médecin
        $patients = Patient::getPatientsForDoctor(
            (int) $_SESSION['user']['id']
        );

        /// Patient sélectionné via URL
        $doctorPatients = array_column(Patient::getPatientsForDoctor($_SESSION['user']['id']), 'pt_id');

        if (isset($_GET['patient']) && ctype_digit($_GET['patient'])) {
            $requestedId = (int) $_GET['patient'];

            // Patient autorisé : on actualise la page sinon on reste sur le patient actuel
            if (in_array($requestedId, $doctorPatients, true)) {
                $_SESSION['last_patient_id'] = $requestedId;
            }
        }

        $patientId = $_SESSION['last_patient_id']
            ?? ($patients[0]['pt_id'] ?? null);

        $patient = $patientId
            ? Patient::findById($patientId)
            : null;

        // Affichage d'un patient par défaut : premier patient du médecin
        if ($patientId === null) {
            $patientId = Patient::getFirstPatientIdForDoctor((int) $_SESSION['user']['id']);

            if ($patientId !== null) {
                $_SESSION['last_patient_id'] = $patientId;
            }
        }

        // Charger le patient uniquement si un ID existe
        $patient = null;
        if ($patientId !== null) {
            $patient = Patient::findById($patientId);
        }

        // Aucun patient : dashboard vide
        if ($patientId === null || $patient === null) {
            $chartData = [];
            require __DIR__ . '/../Views/dashboard.php';
            return;
        }

        // Données graphiques (type/intervalles des ordonnées/unité)
        $chartData = [];

        // Température corporelle (35–40 °C)
        if ($data = Patient::getChartData($patientId, 'Température corporelle', 50)) {
            $chartData['temperature'] = [
                'values'    => Patient::prepareChartValues($data['valeurs'], 35.0, 40.0),
                'lastValue' => end($data['valeurs'])['valeur'],
                'unit'      => $data['unite'],
                'seuil_preoccupant' => Patient::getSeuilByStatus($patientId, 'Température corporelle', 'préoccupant', true),
                'seuil_urgent'      => Patient::getSeuilByStatus($patientId, 'Température corporelle', 'urgent', true),
                'seuil_critique'    => Patient::getSeuilByStatus($patientId, 'Température corporelle', 'critique', true),
                'seuil_preoccupant_min' => Patient::getSeuilByStatus($patientId, 'Température corporelle', 'préoccupant', false),
                'seuil_urgent_min'      => Patient::getSeuilByStatus($patientId, 'Température corporelle', 'urgent', false),
                'seuil_critique_min'    => Patient::getSeuilByStatus($patientId, 'Température corporelle', 'critique', false)
            ];
        }

        // Tension artérielle (100–140 mmHg)
        $data = Patient::getChartData($patientId, 'Tension arterielle', 50)
            ?: Patient::getChartData($patientId, 'Tension artérielle', 50);

        if ($data) {
            $chartData['blood-pressure'] = [
                'values'    => Patient::prepareChartValues($data['valeurs'], 100, 140),
                'lastValue' => end($data['valeurs'])['valeur'],
                'unit'      => $data['unite'],
                'seuil_preoccupant' => Patient::getSeuilByStatus($patientId, 'Tension arterielle', 'préoccupant', true),
                'seuil_urgent'      => Patient::getSeuilByStatus($patientId, 'Tension arterielle', 'urgent', true),
                'seuil_critique'    => Patient::getSeuilByStatus($patientId, 'Tension arterielle', 'critique', true),
                'seuil_preoccupant_min' => Patient::getSeuilByStatus($patientId, 'Tension arterielle', 'préoccupant', false),
                'seuil_urgent_min'      => Patient::getSeuilByStatus($patientId, 'Tension arterielle', 'urgent', false),
                'seuil_critique_min'    => Patient::getSeuilByStatus($patientId, 'Tension arterielle', 'critique', false)
            ];
        }

        // Fréquence cardiaque (25–100 bpm)
        if ($data = Patient::getChartData($patientId, 'Fréquence cardiaque', 50)) {
            $chartData['heart-rate'] = [
                'values' => Patient::prepareChartValues($data['valeurs'], 25, 100),
                'lastValue' => end($data['valeurs'])['valeur'],
                'unit' => $data['unite'],
                'seuil_preoccupant' => Patient::getSeuilByStatus($patientId, 'Fréquence cardiaque', 'préoccupant', true),
                'seuil_urgent'      => Patient::getSeuilByStatus($patientId, 'Fréquence cardiaque', 'urgent', true),
                'seuil_critique'    => Patient::getSeuilByStatus($patientId, 'Fréquence cardiaque', 'critique', true),
                'seuil_preoccupant_min' => Patient::getSeuilByStatus($patientId, 'Fréquence cardiaque', 'préoccupant', false),
                'seuil_urgent_min'      => Patient::getSeuilByStatus($patientId, 'Fréquence cardiaque', 'urgent', false),
                'seuil_critique_min'    => Patient::getSeuilByStatus($patientId, 'Fréquence cardiaque', 'critique', false)
            ];
        }

        // Fréquence respiratoire (0–20 resp/min)
        if ($data = Patient::getChartData($patientId, 'Fréquence respiratoire', 50)) {
            $chartData['respiration'] = [
                'values'    => Patient::prepareChartValues($data['valeurs'], 0, 20),
                'lastValue' => end($data['valeurs'])['valeur'],
                'unit'      => $data['unite'],
                'seuil_preoccupant' => Patient::getSeuilByStatus($patientId, 'Fréquence respiratoire', 'préoccupant', true),
                'seuil_urgent'      => Patient::getSeuilByStatus($patientId, 'Fréquence respiratoire', 'urgent', true),
                'seuil_critique'    => Patient::getSeuilByStatus($patientId, 'Fréquence respiratoire', 'critique', true),
                'seuil_preoccupant_min' => Patient::getSeuilByStatus($patientId, 'Fréquence respiratoire', 'préoccupant', false),
                'seuil_urgent_min'      => Patient::getSeuilByStatus($patientId, 'Fréquence respiratoire', 'urgent', false),
                'seuil_critique_min'    => Patient::getSeuilByStatus($patientId, 'Fréquence respiratoire', 'critique', false)
            ];
        }

        // Glycémie (4.0–7.5 mmol/L)
        if ($data = Patient::getChartData($patientId, 'Glycémie', 50)) {
            $chartData['glucose-trend'] = [
                'values'    => Patient::prepareChartValues($data['valeurs'], 3, 7.5),
                'lastValue' => end($data['valeurs'])['valeur'],
                'unit'      => $data['unite'],
                'seuil_preoccupant' => Patient::getSeuilByStatus($patientId, 'Glycémie', 'préoccupant', true),
                'seuil_urgent'      => Patient::getSeuilByStatus($patientId, 'Glycémie', 'urgent', true),
                'seuil_critique'    => Patient::getSeuilByStatus($patientId, 'Glycémie', 'critique', true),
                'seuil_preoccupant_min' => Patient::getSeuilByStatus($patientId, 'Glycémie', 'préoccupant', false),
                'seuil_urgent_min'      => Patient::getSeuilByStatus($patientId, 'Glycémie', 'urgent', false),
                'seuil_critique_min'    => Patient::getSeuilByStatus($patientId, 'Glycémie', 'critique', false)
            ];
        }

        // Poids (35–110 kg)
        if ($data = Patient::getChartData($patientId, 'Poids', 50)) {
            $chartData['weight'] = [
                'values'    => Patient::prepareChartValues($data['valeurs'], 35, 110),
                'lastValue' => end($data['valeurs'])['valeur'],
                'unit'      => $data['unite'],
                'seuil_preoccupant' => Patient::getSeuilByStatus($patientId, 'Poids', 'préoccupant', true),
                'seuil_urgent'      => Patient::getSeuilByStatus($patientId, 'Poids', 'urgent', true),
                'seuil_critique'    => Patient::getSeuilByStatus($patientId, 'Poids', 'critique', true),
                'seuil_preoccupant_min' => Patient::getSeuilByStatus($patientId, 'Poids', 'préoccupant', false),
                'seuil_urgent_min'      => Patient::getSeuilByStatus($patientId, 'Poids', 'urgent', false),
                'seuil_critique_min'    => Patient::getSeuilByStatus($patientId, 'Poids', 'critique', false)
            ];
        }

        // Saturation en oxygène (90–100 %)
        if ($data = Patient::getChartData($patientId, 'Saturation en oxygène', 50)) {
            $chartData['oxygen-saturation'] = [
                'values'    => Patient::prepareChartValues($data['valeurs'], 90, 100),
                'lastValue' => end($data['valeurs'])['valeur'],
                'unit'      => $data['unite'],
                'seuil_preoccupant' => Patient::getSeuilByStatus($patientId, 'Saturation en oxygène', 'préoccupant', true),
                'seuil_urgent'      => Patient::getSeuilByStatus($patientId, 'Saturation en oxygène', 'urgent', true),
                'seuil_critique'    => Patient::getSeuilByStatus($patientId, 'Saturation en oxygène', 'critique', true),
                'seuil_preoccupant_min' => Patient::getSeuilByStatus($patientId, 'Saturation en oxygène', 'préoccupant', false),
                'seuil_urgent_min'      => Patient::getSeuilByStatus($patientId, 'Saturation en oxygène', 'urgent', false),
                'seuil_critique_min'    => Patient::getSeuilByStatus($patientId, 'Saturation en oxygène', 'critique', false)
            ];
        }

        // Affichage
        require __DIR__ . '/../Views/dashboard.php';
    }
}
