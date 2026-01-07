<?php

namespace Models;

use Core\Database;
use PDO;

/**
 * Class HistoriqueConsole
 *
 * Gère l'enregistrement des actions des médecins dans l'historique.
 * Table : historique_console
 * Colonnes : log_id, med_id, type_action, date_action, heure_action
 *
 * @package Models
 */
final class HistoriqueConsole
{
    /**
     * Types d'action valides
     */
    private const VALID_ACTIONS = [
        'ouvrir',
        'réduire'
    ];

    /**
     * Enregistre une action du médecin dans l'historique.
     *
     * @param int $medId ID du médecin
     * @param string $typeAction Type d'action ('ouvrir' ou 'réduire')
     * @return bool True si l'insertion a réussi, false sinon
     */
    public static function log(int $medId, string $typeAction): bool
    {
        $pdo = Database::getConnection();
        
        // Valider le type d'action
        if (!in_array($typeAction, self::VALID_ACTIONS, true)) {
            error_log(sprintf('[HISTORIQUE] Type d\'action invalide: %s', $typeAction));
            return false;
        }
        
        $dateAction = date('Y-m-d');
        $heureAction = date('H:i:s');
        // Générer un log_id unique basé sur timestamp + random
        $logId = (int)(microtime(true) * 10000) + random_int(1, 999);
        
        try {
            $st = $pdo->prepare('
                INSERT INTO historique_console (log_id, med_id, type_action, date_action, heure_action)
                VALUES (?, ?, ?, ?, ?)
            ');
            
            $result = $st->execute([$logId, $medId, $typeAction, $dateAction, $heureAction]);
            
            if (!$result) {
                error_log(sprintf('[HISTORIQUE] Erreur INSERT: med_id=%d type=%s', $medId, $typeAction));
            }
            
            return $result;
        } catch (\Throwable $e) {
            error_log(sprintf('[HISTORIQUE] Exception: %s', $e->getMessage()));
            return false;
        }
    }

    /**
     * Enregistre l'agrandissement ou la récupération d'un graphique (action 'ouvrir').
     *
     * @param int $medId ID du médecin
     * @return bool
     */
    public static function logGraphiqueOuvrir(int $medId): bool
    {
        return self::log($medId, 'ouvrir');
    }

    /**
     * Enregistre le rapetissement ou la suppression d'un graphique (action 'réduire').
     *
     * @param int $medId ID du médecin
     * @return bool
     */
    public static function logGraphiqueReduire(int $medId): bool
    {
        return self::log($medId, 'réduire');
    }

    /**
     * Récupère l'historique d'un médecin (optionnel, pour consultation interne/admin).
     *
     * @param int $medId ID du médecin
     * @param int $limit Nombre de logs à récupérer
     * @return array Liste des logs
     */
    public static function getHistoryByMedId(int $medId, int $limit = 100): array
    {
        $pdo = Database::getConnection();
        
        $st = $pdo->prepare('
            SELECT 
                log_id,
                med_id,
                type_action,
                date_action,
                heure_action
            FROM historique_console
            WHERE med_id = ?
            ORDER BY date_action DESC, heure_action DESC
            LIMIT ?
        ');
        
        $st->bindValue(1, $medId, PDO::PARAM_INT);
        $st->bindValue(2, $limit, PDO::PARAM_INT);
        $st->execute();
        
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }
}
