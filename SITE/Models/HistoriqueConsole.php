<?php

namespace Models;

use Core\Database;
use PDO;

/**
 * Modèle HistoriqueConsole - Logging des actions des médecins.
 *
 *Enregistre les interactions des médecins avec les graphiques du dashboard
 * dans la table 'historique_console'.
 *
 * @package Models
 */
final class HistoriqueConsole
{
    /**
     * Types d'action valides
     *
     * Liste des actions loggables sur les graphiques.
     *
     * @var array<int,string>
     */
    private const VALID_ACTIONS = [
        'ajouter',
        'supprimer',
        'réduire',
        'agrandir'
    ];

    /**
     * Enregistre une action du médecin dans l'historique.
     *
     * Génère un log_id unique basé sur timestamp + random pour éviter les collisions.
     * Valide le type d'action avant insertion. Log les erreurs si l'insertion échoue.
     *
     * @param int $medId ID du médecin
     * @param string $typeAction Type d'action ('ajouter', 'supprimer', 'réduire', 'agrandir')
     * @param int|null $ptId ID du patient (optionnel)
     * @param int|null $idMesure ID de la mesure/graphique (optionnel)
     * @return bool True si l'insertion a réussi, false sinon
     */
    public static function log(int $medId, string $typeAction, ?int $ptId = null, ?int $idMesure = null): bool
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
                INSERT INTO historique_console 
                    (log_id, med_id, type_action, pt_id, id_mesure, date_action, heure_action)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ');

            $result = $st->execute([$logId, $medId, $typeAction, $ptId, $idMesure, $dateAction, $heureAction]);

            if (!$result) {
                error_log(sprintf(
                    '[HISTORIQUE] Erreur INSERT: med_id=%d type=%s pt_id=%s id_mesure=%s',
                    $medId,
                    $typeAction,
                    $ptId ?? 'null',
                    $idMesure ?? 'null'
                ));
            }

            return $result;
        } catch (\Throwable $e) {
            error_log(sprintf('[HISTORIQUE] Exception: %s', $e->getMessage()));
            return false;
        }
    }

    /**
     * Enregistre l'ajout d'un graphique.
     *
     * @param int $medId ID du médecin
     * @param int|null $ptId ID du patient
     * @param int|null $idMesure ID de la mesure/graphique
     * @return bool
     */
    public static function logGraphiqueAjouter(int $medId, ?int $ptId = null, ?int $idMesure = null): bool
    {
        return self::log($medId, 'ajouter', $ptId, $idMesure);
    }

    /**
     * Enregistre la suppression d'un graphique.
     *
     * @param int $medId ID du médecin
     * @param int|null $ptId ID du patient
     * @param int|null $idMesure ID de la mesure/graphique
     * @return bool
     */
    public static function logGraphiqueSupprimer(int $medId, ?int $ptId = null, ?int $idMesure = null): bool
    {
        return self::log($medId, 'supprimer', $ptId, $idMesure);
    }

    /**
     * Enregistre la réduction de taille d'un graphique.
     *
     * @param int $medId ID du médecin
     * @param int|null $ptId ID du patient
     * @param int|null $idMesure ID de la mesure/graphique
     * @return bool
     */
    public static function logGraphiqueReduire(int $medId, ?int $ptId = null, ?int $idMesure = null): bool
    {
        return self::log($medId, 'réduire', $ptId, $idMesure);
    }

    /**
     * Enregistre l'agrandissement d'un graphique.
     *
     * @param int $medId ID du médecin
     * @param int|null $ptId ID du patient
     * @param int|null $idMesure ID de la mesure/graphique
     * @return bool
     */
    public static function logGraphiqueAgrandir(int $medId, ?int $ptId = null, ?int $idMesure = null): bool
    {
        return self::log($medId, 'agrandir', $ptId, $idMesure);
    }

    /**
     * Récupère l'historique d'un médecin (optionnel, pour consultation interne/admin, analyse d'utilsation).
     *
     * Retourne les N dernières actions d'un médecin, triées de la plus récente
     * à la plus ancienne. Utilisé pour l'analyse d'utilisation.
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
                pt_id,
                id_mesure,
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
