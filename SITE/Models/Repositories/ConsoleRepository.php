<?php

namespace Models\Repositories;

use Core\Database;
use Models\Entities\ConsoleLog;
use PDO;

/**
 * Dépôt de l'Historique Console
 *
 * Gère l'enregistrement et la lecture des actions effectuées par le médecin
 * sur son tableau de bord (ajouter un graphique, réduire une fenêtre)
 *
 * Sert a garder une trace de l'activité
 *
 * @package Models\Repositories
 */
class ConsoleRepository
{
    /**
     * @var PDO Connexion à la base de données
     */
    private PDO $db;

    /**
     * Liste des actions autorisées
     */
    private const VALID_ACTIONS = ['ajouter', 'supprimer', 'réduire', 'agrandir'];

    /**
     * Constructeur : Initialise la connexion à la base de données
     */
    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Enregistre une nouvelle action dans l'historique
     *
     * Vérifie d'abord si l'action est valide (dans la liste VALID_ACTIONS)
     * Génère un identifiant unique basé sur le temps actuel
     *
     * @param int      $medId      L'identifiant du médecin qui fait l'action
     * @param string   $typeAction Le type d'action (ex: 'ajouter')
     * @param int|null $ptId       (Optionnel) ID du patient concerné
     * @param int|null $idMesure   (Optionnel) ID de la mesure concernée
     *
     * @return bool Vrai si l'enregistrement a réussi, Faux sinon
     */
    public function log(int $medId, string $typeAction, ?int $ptId = null, ?int $idMesure = null): bool
    {
        // Sécurité : On refuse les actions inconnues
        if (!in_array($typeAction, self::VALID_ACTIONS, true)) {
            return false;
        }

        // Génération d'un ID unique basé sur le temps (microsecondes) + hasard
        $logId = (int)(microtime(true) * 10000) + random_int(1, 999);

        try {
            $stmt = $this->db->prepare('
                INSERT INTO historique_console 
                    (log_id, med_id, type_action, pt_id, id_mesure, date_action, heure_action)
                VALUES (?, ?, ?, ?, ?, NOW(), NOW())
            ');
            return $stmt->execute([$logId, $medId, $typeAction, $ptId, $idMesure]);
        } catch (\Throwable $e) {
            // En cas d'erreur SQL, on note l'erreur dans les logs serveur mais on ne plante pas le site
            error_log('[CONSOLE_REPO] ' . $e->getMessage());
            return false;
        }
    }

    // --- Méthodes d'aide (Raccourcis) ---

    /**
     * Raccourci pour enregistrer un ajout
     */
    public function logAjouter(int $medId, ?int $ptId, ?int $idMesure): bool {
        return $this->log($medId, 'ajouter', $ptId, $idMesure);
    }

    /**
     * Raccourci pour enregistrer une suppression
     */
    public function logSupprimer(int $medId, ?int $ptId, ?int $idMesure): bool {
        return $this->log($medId, 'supprimer', $ptId, $idMesure);
    }

    /**
     * Raccourci pour enregistrer une réduction de fenêtre
     */
    public function logReduire(int $medId, ?int $ptId, ?int $idMesure): bool {
        return $this->log($medId, 'réduire', $ptId, $idMesure);
    }

    /**
     * Raccourci pour enregistrer un agrandissement de fenêtre
     */
    public function logAgrandir(int $medId, ?int $ptId, ?int $idMesure): bool {
        return $this->log($medId, 'agrandir', $ptId, $idMesure);
    }

    /**
     * Récupère l'historique complet d'un médecin
     *
     * Trie les résultats du plus récent au plus ancien
     *
     * @param int $medId L'ID du médecin
     * @param int $limit Nombre maximum d'entrées à récupérer (défaut 100)
     *
     * @return array Une liste d'objets ConsoleLog
     */
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