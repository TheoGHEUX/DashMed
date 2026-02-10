<?php

namespace Models\Repositories;

use Core\Database;
use Models\Entities\ConsoleLog;
use PDO;

class ConsoleRepository
{
    private PDO $db;

    private const VALID_ACTIONS = ['ajouter', 'supprimer', 'réduire', 'agrandir'];

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function log(int $medId, string $typeAction, ?int $ptId = null, ?int $idMesure = null): bool
    {
        if (!in_array($typeAction, self::VALID_ACTIONS, true)) {
            return false;
        }

        $logId = (int)(microtime(true) * 10000) + random_int(1, 999);

        try {
            $stmt = $this->db->prepare('
                INSERT INTO historique_console 
                    (log_id, med_id, type_action, pt_id, id_mesure, date_action, heure_action)
                VALUES (?, ?, ?, ?, ?, NOW(), NOW())
            ');
            return $stmt->execute([$logId, $medId, $typeAction, $ptId, $idMesure]);
        } catch (\Throwable $e) {
            error_log('[CONSOLE_REPO] ' . $e->getMessage());
            return false;
        }
    }

    // Helpers
    public function logAjouter(int $medId, ?int $ptId, ?int $idMesure): bool {
        return $this->log($medId, 'ajouter', $ptId, $idMesure);
    }

    public function logSupprimer(int $medId, ?int $ptId, ?int $idMesure): bool {
        return $this->log($medId, 'supprimer', $ptId, $idMesure);
    }

    public function logReduire(int $medId, ?int $ptId, ?int $idMesure): bool {
        return $this->log($medId, 'réduire', $ptId, $idMesure);
    }

    public function logAgrandir(int $medId, ?int $ptId, ?int $idMesure): bool {
        return $this->log($medId, 'agrandir', $ptId, $idMesure);
    }

    public function getHistoryByMedId(int $medId, int $limit = 100): array
    {
        $stmt = $this->db->prepare('
            SELECT * FROM historique_console
            WHERE med_id = ?
            ORDER BY date_action DESC, heure_action DESC
            LIMIT ?
        ');
        $stmt->bindValue(1, $medId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();

        $logs = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $logs[] = new ConsoleLog($row);
        }
        return $logs;
    }
}