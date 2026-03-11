<?php

declare(strict_types=1);

namespace App\Models\ConsoleLog\Repositories;

use Core\Database;
use App\Models\ConsoleLog\Interfaces\IActionLoggerRepository;
use PDO;

class ActionLoggerRepository implements IActionLoggerRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Enregistre une action dans la table historique_console
     */
    public function log(int $medId, string $typeAction, int $typeActionId, ?int $ptId = null, ?int $idMesure = null): bool
    {
        try {
            // Génération d'un ID unique basé sur le timestamp (comme dans main)
            $logId = (int)(microtime(true) * 10000) + random_int(1, 999);

            $stmt = $this->db->prepare("
                INSERT INTO historique_console 
                    (log_id, med_id, type_action, type_action_id, pt_id, id_mesure, date_action, heure_action) 
                VALUES (:log_id, :med_id, :type_action, :type_action_id, :pt_id, :id_mesure, NOW(), NOW())
            ");

            return $stmt->execute([
                ':log_id'          => $logId,
                ':med_id'          => $medId,
                ':type_action'     => $typeAction,
                ':type_action_id'  => $typeActionId,
                ':pt_id'           => $ptId,
                ':id_mesure'       => $idMesure
            ]);
        } catch (\Throwable $e) {
            // En cas d'erreur SQL, on log mais on ne plante pas
            error_log('[ACTION_LOGGER_REPO] ' . $e->getMessage());
            return false;
        }
    }
}