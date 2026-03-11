<?php

declare(strict_types=1);

namespace App\Models\ConsoleLog\Services;

use App\Models\ConsoleLog\Interfaces\ITreePredictor;

/**
 * Service de prédiction d'actions via arbre de décision (Python)
 */
class TreePredictor implements ITreePredictor
{
    private string $scriptPath;

    public function __construct()
    {
        // Chemin absolu vers ton script predict_action.py
        $this->scriptPath = __DIR__ . '/../../../Scripts/predict_action.py';
    }

    /**
     * Prédit la prochaine action en appelant le script Python.
     * @param array $history Tableau d'objets ConsoleLog
     */
    public function predict(array $history): ?string
    {
        if (empty($history)) {
            return null;
        }

        // 1. Récupérer la dernière action effectuée pour prédire la SUIVANTE
        // On suppose que l'historique est trié du plus récent au plus ancien ($history[0] est le dernier)
        $lastLog = $history[0];

        // 2. Préparer les arguments pour Python
        // Usage: python predict_action.py <action> <type_mesure> <heure> <position>

        $action = $lastLog->getTypeAction(); // ex: "ajouter"

        // ⚠️ ATTENTION : Ton script Python attend le NOM de la mesure (ex: "Tension artérielle")
        // Mais ton objet ConsoleLog n'a peut-être que l'ID ($lastLog->getMesureId()).
        // Idéalement, il faudrait récupérer le nom via une jointure SQL.
        // Pour l'instant, on passe une valeur par défaut ou l'ID si le Python le gère.
        $mesure = "Tension artérielle"; // TODO: Récupérer le vrai nom de la mesure via le Repo

        $heure = (int)substr($lastLog->getHeure(), 0, 2);
        $position = 0; // On simplifie pour l'instant (ou calculable via count($history))

        // 3. Construction de la commande Shell
        // On utilise escapeshellarg pour la sécurité
        $cmd = sprintf(
            'python3 %s %s %s %d %d',
            escapeshellarg($this->scriptPath),
            escapeshellarg($action),
            escapeshellarg($mesure),
            $heure,
            $position
        );

        // 4. Exécution
        $output = shell_exec($cmd);

        if (!$output) {
            error_log("[TreePredictor] Erreur : Pas de réponse du script Python.");
            return null;
        }

        // 5. Décodage du JSON renvoyé par Python
        $result = json_decode($output, true);

        if ($result && isset($result['success']) && $result['success'] === true) {
            // On retourne l'action prédite (ex: "supprimer")
            return $result['prediction']['action'] ?? null;
        } else {
            // En cas d'erreur Python (ex: modèle pas encore entraîné)
            error_log("[TreePredictor] Erreur Python : " . ($result['error'] ?? $output));
            return null;
        }
    }
}