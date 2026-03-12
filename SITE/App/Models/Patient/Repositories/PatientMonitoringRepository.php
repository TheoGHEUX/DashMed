<?php

declare(strict_types=1);

namespace App\Models\Patient\Repositories;

use Core\Database;
use App\Models\Patient\Interfaces\IPatientMonitoringRepository;
use PDO;

/**
 * Repository pour la gestion des mesures et seuils de suivi patient.
 *
 * Un repository est une classe qui fait le lien entre le code métier et la base de données.
 * Il centralise les requêtes SQL et permet de manipuler les données de façon structurée.
 */
final class PatientMonitoringRepository implements IPatientMonitoringRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getChartData(int $patientId, string $typeMesure, int $limit = 50): ?array
    {
        $stmt = $this->db->prepare('SELECT id_mesure, unite FROM mesures WHERE pt_id = ? AND type_mesure = ? LIMIT 1');
        $stmt->execute([$patientId, $typeMesure]);
        $mesure = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$mesure) {
            return null;
        }

        $stmt = $this->db->prepare('
            SELECT valeur, date_mesure, heure_mesure 
            FROM valeurs_mesures 
            WHERE id_mesure = ? 
            ORDER BY date_mesure DESC, heure_mesure DESC 
            LIMIT ?
        ');
        $stmt->execute([$mesure['id_mesure'], $limit]);
        $valeurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'id_mesure' => $mesure['id_mesure'],
            'type_mesure' => $typeMesure,
            'unite' => $mesure['unite'],
            'valeurs' => array_reverse($valeurs)
        ];
    }

    public function getAllSeuilsForMetric(int $patientId, string $typeMesure): array
    {
        $stmt = $this->db->prepare("
            SELECT sa.statut, sa.majorant, sa.seuil
            FROM seuil_alerte sa
            JOIN mesures m ON m.id_mesure = sa.id_mesure
            WHERE m.type_mesure = ? AND m.pt_id = ?
        ");
        $stmt->execute([$typeMesure, $patientId]);

        $result = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $statut = str_replace(['é', 'è'], ['e', 'e'], strtolower($row['statut']));
            $key = 'seuil_' . $statut . ((int)$row['majorant'] === 0 ? '_min' : '');
            $result[$key] = (float)$row['seuil'];
        }
        return $result;
    }

    /**
     * Génère une nouvelle valeur pour chaque mesure active du patient.
     */
    public function generateSimulationData(int $patientId): int
    {
        // Récupérer les types de mesures suivis par ce patient
        $stmt = $this->db->prepare("SELECT id_mesure, type_mesure FROM mesures WHERE pt_id = ?");
        $stmt->execute([$patientId]);
        $mesures = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($mesures)) {
            return 0;
        }

        $count = 0;

        foreach ($mesures as $mesure) {
            $id_mesure = $mesure['id_mesure'];

            // Récupérer la dernière valeur
            $stmt_last = $this->db->prepare("
                SELECT valeur 
                FROM valeurs_mesures 
                WHERE id_mesure = ? 
                ORDER BY date_mesure DESC, heure_mesure DESC 
                LIMIT 1
            ");
            $stmt_last->execute([$id_mesure]);
            $last = $stmt_last->fetchColumn();

            $valeur_base = $last !== false ? (float)$last : 70;

            $variation = mt_rand(-5, 5) / 10;
            $valeur = round($valeur_base + $variation, 1);

            // Insertion
            $insert = $this->db->prepare("
                INSERT INTO valeurs_mesures 
                (valeur, date_mesure, heure_mesure, id_mesure)
                VALUES (?, CURDATE(), CURTIME(), ?)
            ");

            if ($insert->execute([$valeur, $id_mesure])) {
                $count++;
            }
        }

        return $count;
    }
}
