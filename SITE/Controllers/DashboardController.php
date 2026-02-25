<?php

namespace Controllers;

use Models\Repositories\PatientRepository;
use Models\Repositories\ConsoleRepository;

/**
 * Tableau de bord
 *
 * Prépare et affiche les données des patients suivis par le médecin connecté
 * avec leurs graphiques (température, tension, fréquence cardiaque, etc.)
 * incluant les seuils d'alerte.
 *
 * @package Controllers
 */
final class DashboardController
{
    private PatientRepository $patientRepo;

    /**
     * Configuration des métriques médicales avec leurs plages de valeurs.
     *
     * Chaque métrique contient :
     * - label    : nom affiché de la métrique
     * - labelAlt : nom alternatif (fallback)
     * - min      : valeur minimale pour la normalisation des graphiques
     * - max      : valeur maximale pour la normalisation des graphiques
     *
     * @var array<string,array>
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
     * Constructeur : Initialise l'accès aux données
     */
    public function __construct()
    {
        $this->patientRepo = new PatientRepository();
    }

    /**
     * Affiche la page du tableau de bord avec graphiques et infos patients.
     *
     * Fonctionnement :
     * 1. Vérifie l'authentification (redirige vers /login sinon)
     * 2. Récupère les patients suivis par le médecin
     * 3. Si aucun patient → affiche une icône SVG avec message
     * 4. Détermine le patient sélectionné (URL, session ou premier de la liste)
     * 5. Vérifie que le patient est autorisé pour ce médecin
     * 6. Récupère les données de toutes les métriques avec seuils d'alerte
     * 7. Affiche la vue dashboard.php
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

        // 1. Récupération des patients via le Repository (Retourne des OBJETS Patient)
        $patientsObjects = $this->patientRepo->getPatientsForDoctor((int) $_SESSION['user']['id']);

        // 2. Conversion en tableaux pour compatibilité avec la vue actuelle
        $patients = array_map(fn($p) => $p->toArray(), $patientsObjects);

        // Si aucun patient associé, afficher le message avec icône
        if (empty($patients)) {
            // error_log("DEBUG: Aucun patient - affichage icône SVG");
            $noPatient = true;
            $chartData = [];
            require __DIR__ . '/../Views/connected/dashboard.php';
            return;
        }

        // error_log("DEBUG: Patients trouvés: " . count($patients));

        /// Patient sélectionné via URL
        $doctorPatients = array_column($patients, 'pt_id');

        if (isset($_GET['patient']) && ctype_digit($_GET['patient'])) {
            $requestedId = (int) $_GET['patient'];

            // Patient autorisé : on actualise la page sinon on reste sur le patient actuel
            if (in_array($requestedId, $doctorPatients, true)) {
                $_SESSION['last_patient_id'] = $requestedId;
            }
        }

        $patientId = $_SESSION['last_patient_id'] ?? $patients[0]['pt_id'];

        // Récupération via Repository
        $patientObj = $this->patientRepo->findById($patientId);
        $patient = $patientObj ? $patientObj->toArray() : null;

        // Sécurité : si le patient n'existe plus ou n'est pas autorisé
        if ($patient === null || !in_array($patientId, $doctorPatients, true)) {
            $patientId = $patients[0]['pt_id'];
            // Repli sur le premier patient
            $patientObj = $this->patientRepo->findById($patientId);
            $patient = $patientObj ? $patientObj->toArray() : null;
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
        require __DIR__ . '/../Views/connected/dashboard.php';
    }

    /**
     * Récupère les données d'une métrique avec ses seuils.
     *
     * Processus :
     * 1. Récupère les 50 dernières valeurs de la métrique
     * 2. Tente un repli (fallback) sur le label alternatif si pas de données
     * 3. Normalise les valeurs entre 0 et 1 selon min/max
     * 4. Récupère les seuils d'alerte (préoccupant, urgent, critique)
     * 5. Retourne un tableau formaté pour Chart.js
     *
     * @param int         $patientId   ID du patient
     * @param string      $metricLabel Nom de la métrique
     * @param string|null $labelAlt    Nom alternatif (fallback)
     * @param float       $minValue    Valeur minimale pour normalisation
     * @param float       $maxValue    Valeur maximale pour normalisation
     * @return array|null Données formatées ou null si pas de données
     */
    private function getMetricChartData(
        int $patientId,
        string $metricLabel,
        ?string $labelAlt,
        float $minValue,
        float $maxValue
    ): ?array {
        // Appel Repo (Instance)
        $data = $this->patientRepo->getChartData($patientId, $metricLabel, 50);

        // Fallback pour label alternatif
        if (!$data && $labelAlt) {
            $data = $this->patientRepo->getChartData($patientId, $labelAlt, 50);
            $metricLabel = $labelAlt;
        }

        // Vérifier que les données existent
        if (!$data || !isset($data['valeurs']) || !is_array($data['valeurs']) || empty($data['valeurs'])) {
            return null;
        }

        $valeurs = $data['valeurs'];
        $lastValueRow = end($valeurs);

        if (!is_array($lastValueRow) || !isset($lastValueRow['valeur'])) {
            return null;
        }

        $lastValue = $lastValueRow['valeur'];

        // Construction du résultat avec appels Repo pour les calculs et seuils
        $result = [
            'id_mesure' => $data['id_mesure'] ?? null,
            'values' => $this->patientRepo->prepareChartValues($data['valeurs'], $minValue, $maxValue),
            'lastValue' => $lastValue,
            'unit' => $data['unite'] ?? '',
            'seuil_preoccupant' => $this->patientRepo->getSeuilByStatus($patientId, $metricLabel, 'préoccupant', true),
            'seuil_urgent' => $this->patientRepo->getSeuilByStatus($patientId, $metricLabel, 'urgent', true),
            'seuil_critique' => $this->patientRepo->getSeuilByStatus($patientId, $metricLabel, 'critique', true),
            'seuil_preoccupant_min' => $this->patientRepo->getSeuilByStatus($patientId, $metricLabel, 'préoccupant', false),
            'seuil_urgent_min' => $this->patientRepo->getSeuilByStatus($patientId, $metricLabel, 'urgent', false),
            'seuil_critique_min' => $this->patientRepo->getSeuilByStatus($patientId, $metricLabel, 'critique', false)
        ];

        return $result;
    }

    /**
     * Endpoint API pour logger les actions sur les graphiques.
     * Utilise ConsoleRepository pour l'insertion.
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
        $medId = (int) $_SESSION['user']['id'];

        $consoleRepo = new ConsoleRepository();
        $success = false;

        switch ($action) {
            case 'ajouter':
                $success = $consoleRepo->logAjouter($medId, $ptId, $idMesure);
                break;
            case 'supprimer':
                $success = $consoleRepo->logSupprimer($medId, $ptId, $idMesure);
                break;
            case 'réduire':
                $success = $consoleRepo->logReduire($medId, $ptId, $idMesure);
                break;
            case 'agrandir':
                $success = $consoleRepo->logAgrandir($medId, $ptId, $idMesure);
                break;
            default:
                http_response_code(400);
                echo json_encode(['error' => 'Action invalide']);
                exit;
        }

        if ($success) {
            echo json_encode(['success' => true, 'action' => $action]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Échec de l\'enregistrement']);
        }
        exit;
    }

    /**
     * Endpoint API pour récupérer l'agencement du dashboard d'un patient
     */
    public function getLayout(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (empty($_SESSION['user'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Non authentifié']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Méthode non autorisée']);
            exit;
        }

        $ptId = isset($_GET['ptId']) ? (int)$_GET['ptId'] : null;
        $medId = (int) $_SESSION['user']['id'];

        if (!$ptId) {
            http_response_code(400);
            echo json_encode(['error' => 'ID patient manquant']);
            exit;
        }

        $layout = $this->patientRepo->getDashboardLayout($ptId, $medId);

        if ($layout === null) {
            // Aucun agencement personnalisé, renvoyer la configuration par défaut
            echo json_encode([
                'success' => true,
                'layout' => null,
                'isDefault' => true
            ]);
        } else {
            echo json_encode([
                'success' => true,
                'layout' => $layout,
                'isDefault' => false
            ]);
        }
        exit;
    }

    /**
     * Endpoint API pour sauvegarder l'agencement du dashboard d'un patient
     */
    public function saveLayout(): void
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
        $ptId = isset($input['ptId']) ? (int)$input['ptId'] : null;
        $config = $input['config'] ?? null;
        $medId = (int) $_SESSION['user']['id'];

        if (!$ptId || !is_array($config)) {
            http_response_code(400);
            echo json_encode(['error' => 'Données invalides']);
            exit;
        }

        // Valider la structure de config
        if (!isset($config['visible']) || !is_array($config['visible'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Configuration invalide']);
            exit;
        }

        $success = $this->patientRepo->saveDashboardLayout($ptId, $medId, $config);

        if ($success) {
            echo json_encode(['success' => true]);
        } else {
            error_log("[DASHBOARD] Échec sauvegarde layout - Patient: $ptId, Médecin: $medId");
            http_response_code(500);
            echo json_encode(['error' => 'Échec de la sauvegarde - Le médecin ne suit peut-être pas ce patient']);
        }
        exit;
    }

    /**
     * Endpoint API pour suggérer un layout via KNN (Intelligence Artificielle)
     * Implémentation en PHP pur, sans dépendances externes
     */
    public function suggestLayout(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (empty($_SESSION['user'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Non authentifié']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Méthode non autorisée']);
            exit;
        }

        $ptId = isset($_GET['ptId']) ? (int)$_GET['ptId'] : null;
        $medId = (int) $_SESSION['user']['id'];

        if (!$ptId) {
            http_response_code(400);
            echo json_encode(['error' => 'ID patient manquant']);
            exit;
        }

        $patient = $this->patientRepo->findById($ptId);
        if (!$patient) {
            http_response_code(404);
            echo json_encode(['error' => 'Patient non trouvé']);
            exit;
        }

        // Utiliser l'algorithme KNN implémenté en PHP
        $similarPatients = $this->patientRepo->findSimilarPatients($ptId, $medId, 5);

        if (empty($similarPatients)) {
            echo json_encode([
                'success' => false,
                'message' => 'Aucun patient similaire trouvé avec un agencement personnalisé'
            ]);
            exit;
        }

        // Récupérer le layout du patient le plus similaire (premier dans la liste)
        $mostSimilar = $similarPatients[0];
        $suggestedLayout = $this->patientRepo->getDashboardLayout($mostSimilar['pt_id'], $medId);

        if (!$suggestedLayout) {
            echo json_encode([
                'success' => false,
                'message' => 'Aucun agencement trouvé pour les patients similaires'
            ]);
            exit;
        }

        echo json_encode([
            'success' => true,
            'suggestion' => [
                'similar_patient_id' => $mostSimilar['pt_id'],
                'distance' => round($mostSimilar['distance'], 2),
                'all_similar_patients' => array_map(fn($p) => $p['pt_id'], $similarPatients),
                'layout' => $suggestedLayout
            ]
        ]);
        exit;
    }


    /**
     * Endpoint API pour vérifier si l'IA est disponible
     * Toujours disponible car implémentée en PHP pur
     */
    public function checkAIAvailability(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode([
            'available' => true,
            'implementation' => 'PHP KNN Algorithm',
            'message' => 'IA intégrée, aucune installation requise'
        ]);
        exit;
    }
}
