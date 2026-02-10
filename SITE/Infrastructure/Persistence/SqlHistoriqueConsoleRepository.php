<?php

namespace Infrastructure\Persistence;

use Domain\Repositories\HistoriqueConsoleRepositoryInterface;
use Core\Database;
use PDO;

class SqlHistoriqueConsoleRepository implements HistoriqueConsoleRepositoryInterface
{
    private const VALID_ACTIONS = ['ajouter', 'supprimer', 'réduire', 'agrandir'];
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function log(int $medId, string $typeAction, ?int $ptId = null, ?int $idMesure = null): bool
    {
        if (!in_array($typeAction, self::VALID_ACTIONS, true)) return false;

        $dateAction = date('Y-m-d');
        $heureAction = date('H:i:s');
        $logId = (int)(microtime(true) * 10000) + random_int(1, 999);

        try {
            $st = $this->pdo->prepare('
                INSERT INTO historique_console (log_id, med_id, type_action, pt_id, id_mesure, date_action, heure_action)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ');
            return $st->execute([$logId, $medId, $typeAction, $ptId, $idMesure, $dateAction, $heureAction]);
        } catch (\Throwable $e) {
            error_log(sprintf('[HISTORIQUE] Exception: %s', $e->getMessage()));
            return false;
        }
    }

    public function getHistoryByMedId(int $medId, int $limit = 100): array
    {
        $st = $this->pdo->prepare('
            SELECT log_id, med_id, type_action, pt_id, id_mesure, date_action, heure_action
            FROM historique_console WHERE med_id = ? ORDER BY date_action DESC, heure_action DESC LIMIT ?
        ');
        $st->bindValue(1, $medId, PDO::PARAM_INT);
        $st->bindValue(2, $limit, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }
}
