<?php

namespace Controllers;

use Models\Patient;
use Models\HistoriqueConsole;

/**
 * Contrôleur : Tableau de bord
 *
 * Prépare et affiche les données des patients suivis par le médecin connecté
 * avec leurs graphiques (température, tension, fréquence cardiaque, etc.)
 * incluant les seuils d'alerte.
 *
 * @package Controllers
 */
final class DashboardController
{
    /**
     * Configuration des métriques médicales avec leurs plages de valeurs
     *
     *  Chaque métrique contient :
     *  - label : Nom affiché de la métrique
     *  - labelAlt : Nom alternatif
     *  - min : Valeur minimale pour la normalisation des graphiques
     *  - max : Valeur maximale pour la normalisation des graphiques
     */
    private const METRICS_CONFIG = [
        'temperature' => [
            'label' => 'Température corporelle',
            'min' => 31.0,
            'max' => 42.0
        ],
        'blood-pressure' => [
            'label' => 'Tension artérielle',
            'labelAlt' => 'Tension arterielle',
            'min' => 80,
            'max' => 160
        ],
        'heart-rate' => [
            'label' => 'Fréquence cardiaque',
            'min' => 35,
            'max' => 130
        ],
        'respiration' => [
            'label' => 'Fréquence respiratoire',
            'min' => 0,
            'max' => 30
        ],
        'glucose-trend' => [
            'label' => 'Glycémie',
            'min' => 2,
            'max' => 10
        ],
        'weight' => [
            'label' => 'Poids',
            'min' => 35,
            'max' => 110
        ],
        'oxygen-saturation' => [
            'label' => 'Saturation en oxygène',
            'min' => 72,
            'max' => 100
        ]
    ];

    /**
     * Affiche la page du tableau de bord avec graphiques et infos patients.
     *
     * Fonctionnement :
     *  1. Vérifie l'authentification (redirige vers /login sinon)
     *  2. Récupère les patients suivis par le médecin
     *  3. Si aucun patient → affiche une icône SVG avec message
     *  4. Détermine le patient sélectionné (URL, session ou premier de la liste)
     *  5. Vérifie que le patient est autorisé pour ce médecin
     *  6. Récupère les données de toutes les métriques avec seuils d'alerte
     *  7. Affiche la vue dashboard.php
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

        // Si aucun patient associé, afficher le message avec icône
        if (empty($patients)) {
            error_log("DEBUG: Aucun patient - affichage icône SVG");
            $noPatient = true;
            $chartData = [];
            require __DIR__ . '/../Views/dashboard.php';
            return;
        }

        error_log("DEBUG: Patients trouvés: " . count($patients));

        /// Patient sélectionné via URL
        $doctorPatients = array_column($patients, 'pt_id');

        if (isset($_GET['patient']) && ctype_digit($_GET['patient'])) {
            $requestedId = (int) $_GET['patient'];

            // Patient autorisé : on actualise la page sinon on reste sur le patient actuel
            if (in_array($requestedId, $doctorPatients, true)) {
                $_SESSION['last_patient_id'] = $requestedId;
            }
        }

        $patientId = $_SESSION['last_patient_id']
            ?? $patients[0]['pt_id'];

        $patient = Patient::findById($patientId);

        // Sécurité : si le patient n'existe plus ou n'est pas autorisé
        if ($patient === null || !in_array($patientId, $doctorPatients, true)) {
            $patientId = $patients[0]['pt_id'];
            $patient = Patient::findById($patientId);
            $_SESSION['last_patient_id'] = $patientId;
        }

        // Données graphiques (type/intervalles des ordonnées/unité)
        $chartData = [];
        $noPatient = false;

        foreach (self::METRICS_CONFIG as $key => $config) {
            $metricData = $this->getMetricChartData(
                $patientId,
                $config['label'],
                $config['labelAlt'] ?? null,
                $config['min'],
                $config['max']
            );
            if ($metricData !== null) {
                $chartData[$key] = $metricData;
            }
        }

        // Affichage
        require __DIR__ . '/../Views/dashboard.php';
    }

    /**
     * Récupère les données d'une métrique avec ses seuils
     *
     * Processus :
     *  1. Récupère les 50 dernières valeurs de la métrique
     *  2. Tente un repli (fallback) sur le label alternatif si pas de données
     *  3. Normalise les valeurs entre 0 et 1 selon min/max
     *  4. Récupère les seuils d'alerte (préoccupant, urgent, critique) min et max
     *  5. Retourne un tableau formaté pour Chart.js
     *
     * @param int $patientId ID du patient
     * @param string $metricLabel Nom de la métrique
     * @param string|null $labelAlt Nom alternatif de la métrique (fallback)
     * @param float $minValue Valeur minimale pour le graphique
     * @param float $maxValue Valeur maximale pour le graphique
     * @return array|null Données formatées ou null si pas de données
     */
    private function getMetricChartData(int $patientId, string $metricLabel, ?string $labelAlt, float $minValue, float $maxValue): ?array
    {
        $data = Patient::getChartData($patientId, $metricLabel, 50);

        // Fallback pour label alternatif (ex: Tension arterielle vs Tension artérielle)
        if (!$data && $labelAlt) {
            $data = Patient::getChartData($patientId, $labelAlt, 50);
            $metricLabel = $labelAlt; // Utiliser le label alternatif pour les seuils
        }

        // Vérifier que les données existent ET contiennent un tableau de valeurs
        if (!$data || !isset($data['valeurs']) || !is_array($data['valeurs']) || empty($data['valeurs'])) {
            return null;
        }

        // Créer une copie pour ne pas modifier le tableau original avec end()
        $valeurs = $data['valeurs'];
        $lastValueRow = end($valeurs);

        // Vérifier que lastValueRow est bien un tableau avec la clé 'valeur'
        if (!is_array($lastValueRow) || !isset($lastValueRow['valeur'])) {
            return null;
        }

        $lastValue = $lastValueRow['valeur'];

        $result = [
            'id_mesure' => $data['id_mesure'] ?? null,
            'values' => Patient::prepareChartValues($data['valeurs'], $minValue, $maxValue),
            'lastValue' => $lastValue,
            'unit' => $data['unite'] ?? '',
            'seuil_preoccupant' => Patient::getSeuilByStatus($patientId, $metricLabel, 'préoccupant', true),
            'seuil_urgent' => Patient::getSeuilByStatus($patientId, $metricLabel, 'urgent', true),
            'seuil_critique' => Patient::getSeuilByStatus($patientId, $metricLabel, 'critique', true),
            'seuil_preoccupant_min' => Patient::getSeuilByStatus($patientId, $metricLabel, 'préoccupant', false),
            'seuil_urgent_min' => Patient::getSeuilByStatus($patientId, $metricLabel, 'urgent', false),
            'seuil_critique_min' => Patient::getSeuilByStatus($patientId, $metricLabel, 'critique', false)
        ];

        // Log de debug pour vérifier les seuils
        if ($metricLabel === 'Température corporelle') {
            error_log("DEBUG SEUILS TEMP: " . json_encode($result));
        }

        return $result;
    }

    /**
     * API endpoint pour logger les actions sur les graphiques
     * Attend POST avec JSON: {"action": "ajouter"|"supprimer"|"réduire"|"agrandir", "ptId": int, "idMesure": int}
     *
     * Enregistre l'action dans historique_console via HistoriqueConsole.
     * Retourne JSON :  {"success": true, "action": "..."} ou {"error": "... "}
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
        $ptId = isset($input['ptId']) ? (int)$input['ptId'] : null;
        $idMesure = isset($input['idMesure']) ? (int)$input['idMesure'] : null;

        if (!$action || !in_array($action, ['ajouter', 'supprimer', 'réduire', 'agrandir'], true)) {
            http_response_code(400);
            error_log('[LOG] Action invalide reçue');
            echo json_encode(['error' => 'Action invalide']);
            exit;
        }

        $medId = (int) $_SESSION['user']['id'];

        try {
            $historiqueConsole = new HistoriqueConsole();

            switch ($action) {
                case 'ajouter':
                    $success = $historiqueConsole->logGraphiqueAjouter($medId, $ptId, $idMesure);
                    break;
                case 'supprimer':
                    $success = $historiqueConsole->logGraphiqueSupprimer($medId, $ptId, $idMesure);
                    break;
                case 'réduire':
                    $success = $historiqueConsole->logGraphiqueReduire($medId, $ptId, $idMesure);
                    break;
                case 'agrandir':
                    $success = $historiqueConsole->logGraphiqueAgrandir($medId, $ptId, $idMesure);
                    break;
                default:
                    $success = false;
            }

            if (!$success) {
                error_log(sprintf('[LOG] Échec du log: med_id=%d, action=%s, pt_id=%s, id_mesure=%s', $medId, $action, $ptId ?? 'null', $idMesure ?? 'null'));
                http_response_code(500);
                echo json_encode(['error' => 'Échec de l\'enregistrement']);
                exit;
            }

            echo json_encode(['success' => true, 'action' => $action]);
        } catch (\Exception $e) {
            error_log(sprintf('[LOG] Exception: %s', $e->getMessage()));
            http_response_code(500);
            echo json_encode(['error' => 'Erreur serveur']);
        }
        exit;
    }
}