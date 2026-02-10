<?php

namespace Controllers;

use Core\View;
use Models\Repositories\PatientRepository;
use Models\Repositories\UserRepository;

class DashboardController
{
    private PatientRepository $patientRepo;
    private UserRepository $userRepo;

    public function __construct()
    {
        // On initialise les accès à la BDD via les Repositories
        $this->patientRepo = new PatientRepository();
        $this->userRepo = new UserRepository();
    }

    /**
     * Affiche le tableau de bord principal
     * @param int $medecinId L'ID du médecin connecté (actuellement simulé)
     */
    public function index(int $medecinId = 1): void
    {
        // 1. Récupérer la liste des patients pour la barre latérale
        // Retourne un tableau d'objets Models\Entities\Patient
        $patientsList = $this->patientRepo->findByDoctor($medecinId);

        // 2. Chercher le patient sélectionné (via URL ?id=...) ou prendre le premier par défaut
        $selectedPatient = null;
        $chartData = null;
        $derniersReleves = [];

        if (isset($_GET['patient_id'])) {
            $selectedId = (int)$_GET['patient_id'];
            $selectedPatient = $this->patientRepo->findById($selectedId);
        } elseif (!empty($patientsList)) {
            // Par défaut, on prend le premier de la liste
            $selectedPatient = $patientsList[0];
        }

        // 3. Si un patient est sélectionné, on charge ses données médicales
        if ($selectedPatient) {
            // Exemple : Récupérer les données pour le graphique de température
            // Note : Tu pourras rendre "Température" dynamique via $_GET['mesure'] plus tard
            $chartData = $this->patientRepo->getChartData($selectedPatient->getId(), 'Température');

            // Récupérer les dernières valeurs pour le résumé (cartes en haut du dashboard)
            $derniersReleves = $this->patientRepo->getDernieresValeurs($selectedPatient->getId());
        }

        // 4. Envoyer toutes les données à la vue
        View::render('dashboard_index', [
            'medecin_id'      => $medecinId,
            'patients'        => $patientsList,     // Pour la sidebar
            'currentPatient'  => $selectedPatient,  // Pour le profil au centre
            'chartData'       => $chartData,        // Pour le graph JS
            'derniersReleves' => $derniersReleves   // Pour les cartes
        ]);
    }

    /**
     * Méthode API pour AJAX (si tu veux actualiser le graph sans recharger la page)
     * URL : index.php?controller=dashboard&action=apiGetChart&patient_id=1&mesure=Poids
     */
    public function apiGetChart(): void
    {
        // Vérification basique
        if (!isset($_GET['patient_id']) || !isset($_GET['mesure'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Paramètres manquants']);
            return;
        }

        $id = (int)$_GET['patient_id'];
        $type = $_GET['mesure'];

        $data = $this->patientRepo->getChartData($id, $type);

        header('Content-Type: application/json');
        echo json_encode($data);
        exit; // On arrête le script ici pour ne pas renvoyer de HTML
    }
}