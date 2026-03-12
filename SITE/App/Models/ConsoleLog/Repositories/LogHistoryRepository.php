<?php

declare(strict_types=1);

namespace App\Models\ConsoleLog\Repositories;

use Core\Database;
use App\Models\ConsoleLog\Interfaces\ILogHistoryRepository;
use PDO;

/**
 * Repository pour l'historique des actions du dashboard.
 */
final class LogHistoryRepository implements ILogHistoryRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // On ajoute la valeur par défaut ici aussi pour matcher l'interface
    public function getHistoryByMedId(int $medId, int $limit = 100): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                c.type_action as action, 
                DATE_FORMAT(c.heure_action, '%H') as heure_seule, 
                m.type_mesure as nom_mesure
            FROM historique_console c
            LEFT JOIN mesures m ON c.id_mesure = m.id_mesure
            WHERE c.med_id = :medId
            ORDER BY c.heure_action DESC
            LIMIT :limit
        ");

        $stmt->bindValue(':medId', $medId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_CLASS);
    }
}
