<?php

namespace Controllers;

use Models\Patient;
use Models\HistoriqueConsole;

/**
 * Contrôleur du tableau de bord médecin.
 *
 * Affiche les graphiques de données médicales du patient sélectionné avec
 * seuils d'alerte et historique des interactions.  Gère la sélection du
 * patient et fournit une API pour logger les actions sur les graphiques.
 *
 * @package Controllers
 */
final class DashboardController
{
    /**
     * Configuration des métriques médicales affichées.
     *
     * Définit pour chaque métrique :
     * - label :  Nom affiché de la métrique
     * - labelAlt : Nom alternatif pour compatibilité (ex: accents)
     * - min/max : Plage de valeurs pour la normalisation des graphiques
     *
     * Les plages sont utilisées pour normaliser les données entre 0 et 1
     * afin d'afficher des graphiques cohérents.
     *
     * @var array<string,array<string,mixed>>
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
     * Affiche le tableau de bord avec les graphiques du patient sélectionné.
     *
     * Processus :
     * 1. Vérifie l'authentification (redirige vers /login si non connecté)
     * 2. Récupère la liste des patients suivis par le médecin
     * 3. Affiche un message si aucun patient n'est associé
     * 4. Détermine le patient à afficher (GET, session, ou premier de la liste)
     * 5. Vérifie que le médecin est autorisé à accéder à ce patient
     * 6. Récupère les données de toutes les métriques configurées
     * 7. Affiche la vue dashboard avec graphiques et seuils d'alerte
     *
     * Gestion de la sélection patient :
     * - Paramètre GET patient=ID :  change le patient affiché si autorisé
     * - Session last_patient_id : mémorise le dernier patient consulté
     * - Fallback : premier patient de la liste si aucune sélection
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
            error_log("DEBUG:  Aucun patient - affichage icône SVG");
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
     * Récupère les données d'une métrique avec normalisation et seuils d'alerte.
     *
     * Gère le fallback sur un label alternatif si le label principal n'existe pas
     * (utile pour les problèmes d'accents :  "Tension artérielle" vs "Tension arterielle").
     *
     * Récupère les seuils d'alerte à 3 niveaux (préoccupant, urgent, critique)
     * pour les valeurs hautes (majorant=true) et basses (majorant=false).
     *
     * @param int $patientId ID du patient
     * @param string $metricLabel Nom principal de la métrique
     * @param string|null $labelAlt Nom alternatif (fallback si label principal introuvable)
     * @param float $minValue Valeur minimale pour normalisation (0 sur le graphique)
     * @param float $maxValue Valeur maximale pour normalisation (1 sur le graphique)
     * @return array|null Données formatées (id_mesure, values normalisées, lastValue, unit,
     *                    seuils d'alerte) ou null si aucune donnée disponible
     */
    private function getMetricChartData(int $patientId, string $metricLabel, ?string $labelAlt, float $minValue, float $maxValue): ?array
    {
        $data = Patient:: getChartData($patientId, $metricLabel, 50);

        // Fallback pour label alternatif (ex:  Tension arterielle vs Tension artérielle)
        if (!$data && $labelAlt) {
            $data = Patient::getChartData($patientId, $labelAlt, 50);
            $metricLabel = $labelAlt; // Utiliser le label alternatif pour les seuils
        }

        if (! $data) {
            return null;
        }

        // Créer une copie pour ne pas modifier le tableau original avec end()
        $valeurs = $data['valeurs'];
        $lastValue = end($valeurs)['valeur'];

        $result = [
            'id_mesure' => $data['id_mesure'],
            'values' => Patient::prepareChartValues($data['valeurs'], $minValue, $maxValue),
            'lastValue' => $lastValue,
            'unit' => $data['unite'],
            'seuil_preoccupant' => Patient::getSeuilByStatus($patientId, $metricLabel, 'préoccupant', true),
            'seuil_urgent' => Patient::getSeuilByStatus($patientId, $metricLabel, 'urgent', true),
            'seuil_critique' => Patient::getSeuilByStatus($patientId, $metricLabel, 'critique', true),
            'seuil_preoccupant_min' => Patient::getSeuilByStatus($patientId, $metricLabel, 'préoccupant', false),
            'seuil_urgent_min' => Patient::getSeuilByStatus($patientId, $metricLabel, 'urgent', false),
            'seuil_critique_min' => Patient::getSeuilByStatus($patientId, $metricLabel, 'critique', false)
        ];

        // Log de debug pour vérifier les seuils
        if ($metricLabel === 'Température corporelle' || $metricLabel === 'Température corporelle') {
            error_log("DEBUG SEUILS TEMP: " .  json_encode($result));
        }

        return $result;
    }

    /**
     * API endpoint pour enregistrer les actions utilisateur sur les graphiques.
     *
     * Enregistre dans historique_console les interactions du médecin avec les graphiques
     * (ajout, suppression, réduction, agrandissement) pour audit et analyse.
     *
     * Accepte une requête POST JSON avec action, ptId et idMesure.
     * Valide l'authentification, la méthode HTTP et le type d'action avant enregistrement.
     *
     * Codes de réponse :
     * - 200 :  Succès
     * - 400 : Action invalide
     * - 401 : Non authentifié
     * - 405 : Méthode non autorisée (POST requis)
     * - 500 : Erreur serveur
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

        if (! $action || !in_array($action, ['ajouter', 'supprimer', 'réduire', 'agrandir'], true)) {
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

            if (! $success) {
                error_log(sprintf('[LOG] Échec du log:  med_id=%d, action=%s, pt_id=%s, id_mesure=%s', $medId, $action, $ptId ??  'null', $idMesure ?? 'null'));
                http_response_code(500);
                echo json_encode(['error' => 'Échec de l\'enregistrement']);
                exit;
            }

            echo json_encode(['success' => true, 'action' => $action]);
        } catch (\Exception $e) {
            error_log(sprintf('[LOG] Exception:  %s', $e->getMessage()));
            http_response_code(500);
            echo json_encode(['error' => 'Erreur serveur']);
        }
        exit;
    }
}