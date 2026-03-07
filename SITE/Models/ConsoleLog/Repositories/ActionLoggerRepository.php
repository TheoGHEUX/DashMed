<?php

declare(strict_types=1);

namespace Models\ConsoleLog\Repositories;

use Core\Database;
use Models\ConsoleLog\Interfaces\IActionLoggerRepository;
use PDO;

class ActionLoggerRepository implements IActionLoggerRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function log(int $medId, string $typeAction, int $typeActionId, ?int $ptId = null, ?int $idMesure = null): bool
    {


        $logId = (int)(microtime(true) * 10000) + random_int(1, 999);

        try {
            $stmt = $this->db->prepare('
                INSERT INTO historique_console 
                    (log_id, med_id, type_action, type_action_id, pt_id, id_mesure, date_action, heure_action)
                VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
            ');
            return $stmt->execute([$logId, $medId, $typeAction, $typeActionId, $ptId, $idMesure]);
        } catch (\Throwable $e) {
            error_log('[LOGGER] ' . $e->getMessage());
            return false;
        }
    }
}