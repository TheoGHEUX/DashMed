<?php

declare(strict_types=1);

namespace App\Controllers\Dashboard;

use Core\Controller\AbstractController;
use App\Models\Patient\Factories\PatientUseCaseFactory;
use App\Models\Patient\Repositories\DashboardLayoutRepository;
use App\Models\Patient\UseCases\Dashboard\PatientSimilarityService;
use App\Models\Patient\UseCases\Dashboard\GetDashboardLayout;
use App\Models\Patient\UseCases\Dashboard\SaveDashboardLayout;
use App\Models\Patient\UseCases\Dashboard\SuggestLayout;

/**
 * Contrôleur API pour la gestion et la suggestion d'agencement du dashboard patient (layout).
 *
 * - Permet de charger, sauvegarder et suggérer automatiquement l’agencement de l’interface patient pour un médecin.
 * - Prend en compte la personnalisation utilisateur et l’IA pour proposer des suggestions de mise en page issues de patients similaires.
 */
final class LayoutApiController extends AbstractController
{
    private GetDashboardLayout $getLayout;
    private SaveDashboardLayout $saveLayout;
    private SuggestLayout $suggestLayout;

    /**
     * Prépare les usecases pour le layout du dashboard.
     */
    public function __construct()
    {
        if (class_exists(PatientUseCaseFactory::class)) {
            $this->getLayout = PatientUseCaseFactory::createGetDashboardLayout();
            $this->saveLayout = PatientUseCaseFactory::createSaveDashboardLayout();
            $this->suggestLayout = PatientUseCaseFactory::createSuggestLayout();
            return;
        }


    }

    /**
     * Charge la configuration d’affichage (layout) du dashboard patient pour le médecin connecté.
     */
    public function load(): void
    {
        $this->checkAuth();
        $ptId = (int)($_GET['ptId'] ?? 0);
        $medId = $this->getCurrentUserId();

        if (!$ptId) {
            $this->json(['success' => false, 'error' => 'Patient ID manquant']);
            return;
        }

        // Récupère la configuration de layout. Si n'existe pas, valeur null (=layout par défaut ou suggestion IA)
        $layout = $this->getLayout->execute($ptId, $medId);

        $this->json([
            'success' => true,
            'layout' => $layout,
            'isDefault' => $layout === null
        ]);
    }

    /**
     * Sauvegarde la configuration d’affichage (layout) personnalisée de l’utilisateur.
     */
    public function save(): void
    {
        $this->checkAuth();
        $this->validateApiCsrf();

        $input = $this->getJsonInput();

        $ptId = (int)($input['ptId'] ?? 0);
        $config = $input['config'] ?? null;
        $medId = $this->getCurrentUserId();

        if (!$ptId || !is_array($config)) {
            $this->json(['success' => false, 'error' => 'Données invalides'], 400);
            return;
        }

        // Vérifie que la structure envoyée contient bien les champs attendus
        if (!isset($config['visible']) || !is_array($config['visible'])) {
            $this->json(['success' => false, 'error' => 'Configuration invalide'], 400);
            return;
        }

        // Sauvegarde le usecase retourne true/false selon le succès
        $success = $this->saveLayout->execute($ptId, $medId, $config);

        if ($success) {
            $this->json(['success' => true]);
        } else {
            $this->json(['success' => false, 'error' => 'Échec de la sauvegarde - Le médecin ne suit peut-être pas ce patient'], 500);
        }
    }

    /**
     * Suggère automatiquement un agencement (layout) basé sur les patients similaires.
     */
    public function suggest(): void
    {
        $this->checkAuth();
        $ptId = (int)($_GET['ptId'] ?? 0);
        $medId = $this->getCurrentUserId();

        if (!$ptId) {
            $this->json(['success' => false, 'error' => 'ID patient manquant'], 400);
            return;
        }

        $result = $this->suggestLayout->execute($ptId, $medId);

        if (!empty($result)) {
            $this->json([
                'success' => true,
                'suggestion' => $result
            ]);
        } else {
            $this->json([
                'success' => false,
                'message' => 'Aucune suggestion disponible - Aucun patient similaire avec un agencement personnalisé'
            ]);
        }
    }

    /**
     * Vérifie la disponibilité de la fonctionnalité IA côté serveur.
     */
    public function checkAvailability(): void
    {
        $this->json([
            'available' => true,
            'implementation' => 'PHP KNN Algorithm',
            'message' => 'IA intégrée, aucune installation requise'
        ]);
    }
}