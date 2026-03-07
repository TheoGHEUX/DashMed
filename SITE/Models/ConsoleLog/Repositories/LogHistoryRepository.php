<?php

declare(strict_types=1);

namespace Models\ConsoleLog\Repositories;

use Core\Database;
use Models\ConsoleLog\Entities\ConsoleLog;
use Models\ConsoleLog\Interfaces\ILogHistoryRepository;
use PDO;

class LogHistoryRepository implements ILogHistoryRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Récupère l'historique des actions d'un médecin.
     * Inclut le nom de la mesure associée via une jointure.
     *
     * @return ConsoleLog[]
     */
    public function getHistoryByMedId(int $medId, int $limit = 100): array
    {
        $stmt = $this->db->prepare('
            SELECT 
                h.*, 
                m.type_mesure as nom_mesure
            FROM historique_console h
            LEFT JOIN mesures m ON h.id_mesure = m.id_mesure
            WHERE h.med_id = ?
            ORDER BY h.date_action DESC, h.heure_action DESC
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