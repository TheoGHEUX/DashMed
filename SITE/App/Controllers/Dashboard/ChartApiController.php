<?php

declare(strict_types=1);

namespace Models\Patient\Repositories;

use Core\Database;
use Models\Patient\Interfaces\IPatientMonitoringRepository;
use PDO;

class PatientMonitoringRepository implements IPatientMonitoringRepository
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

        if (!$mesure) return null;

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

        // On retourne les données brutes. Le formatage des clés (seuil_urgent_min...)
        // pourra être fait ici car c'est du mapping SQL->Code, ce qui est acceptable dans un Repo.
        // Si tu veux être ultra-strict, retourne juste $stmt->fetchAll() et le UseCase triera.
        // Pour l'instant, garder le mapping ici est un bon compromis "Infrastructure".

        $result = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $statut = str_replace(['é', 'è'], ['e', 'e'], strtolower($row['statut']));
            $key = 'seuil_' . $statut . ((int)$row['majorant'] === 0 ? '_min' : '');
            $result[$key] = (float)$row['seuil'];
        }
        return $result;
    }
}