<?php

namespace Models\Repositories;

use Core\Database;
use Models\Entities\ConsoleLog;
use PDO;

class ConsoleRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function addLog(string $message, string $level = 'INFO'): bool
    {
        $stmt = $this->db->prepare("INSERT INTO historique_console (message, level, created_at) VALUES (?, ?, NOW())");
        return $stmt->execute([$message, $level]);
    }

    /**
     * @return ConsoleLog[]
     */
    public function getLastLogs(int $limit = 50): array
    {
        $stmt = $this->db->prepare("SELECT * FROM historique_console ORDER BY created_at DESC LIMIT ?");
        $stmt->execute([$limit]);

        $logs = [];
        while ($row = $stmt->fetch()) {
            $logs[] = new ConsoleLog(
                (int)$row['id'],
                $row['message'],
                $row['level'],
                $row['created_at']
            );
        }
        return $logs;
    }
}
