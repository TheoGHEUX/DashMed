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

    // La signature doit être IDENTIQUE à l'interface (noms + defaults)
    public function log(int $medId, string $typeAction, int $typeActionId, ?int $ptId = null, ?int $idMesure = null): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO console_log (med_id, action, id_action, pt_id, id_mesure, heure) 
            VALUES (:med_id, :action, :id_action, :pt_id, :id_mesure, NOW())
        ");

        return $stmt->execute([
            ':med_id'    => $medId,
            ':action'    => $typeAction,   // On utilise le bon nom
            ':id_action' => $typeActionId, // On utilise le bon nom
            ':pt_id'     => $ptId,
            ':id_mesure' => $idMesure
        ]);
    }
}