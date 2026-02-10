<?php

namespace Infrastructure\Persistence;

use Domain\Repositories\PatientRepositoryInterface;
use Core\Database;
use PDO;

class SqlPatientRepository implements PatientRepositoryInterface
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function findById(int $id): ?array
    {
        $st = $this->pdo->prepare('
            SELECT pt_id, prenom, nom, email, sexe, groupe_sanguin, date_naissance, telephone, ville, code_postal, adresse
            FROM patient WHERE pt_id = ? LIMIT 1
        ');
        $st->execute([$id]);
        $patient = $st->fetch(PDO::FETCH_ASSOC);
        return $patient ?: null;
    }

    public function getPatientsForDoctor(int $doctorId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT p.pt_id, p.nom, p.prenom FROM suivre s
            JOIN patient p ON p.pt_id = s.pt_id WHERE s.med_id = :med_id ORDER BY p.nom, p.prenom
        ");
        $stmt->execute([':med_id' => $doctorId]);
        return $stmt->fetchAll();
    }

    public function findByIdForDoctor(int $patientId, int $medId): ?array
    {
        $st = $this->pdo->prepare('
            SELECT p.pt_id, p.prenom, p.nom, p.email, p.sexe, p.groupe_sanguin, p.date_naissance, p.telephone, p.ville, p.code_postal, p.adresse
            FROM patient p JOIN suivre s ON s.pt_id = p.pt_id WHERE p.pt_id = ? AND s.med_id = ? LIMIT 1
        ');
        $st->execute([$patientId, $medId]);
        $patient = $st->fetch(PDO::FETCH_ASSOC);
        return $patient ?: null;
    }

    public function getChartDataForDoctor(int $medId, int $patientId, string $typeMesure, int $limit = 50): ?array
    {
        $check = $this->pdo->prepare('SELECT 1 FROM suivre WHERE med_id = ? AND pt_id = ? LIMIT 1');
        $check->execute([$medId, $patientId]);
        if (!$check->fetch()) return null;

        $st = $this->pdo->prepare('SELECT id_mesure, unite FROM mesures WHERE pt_id = ? AND type_mesure = ? LIMIT 1');
        $st->execute([$patientId, $typeMesure]);
        $mesure = $st->fetch(PDO::FETCH_ASSOC);

        if (!$mesure) return null;

        $st = $this->pdo->prepare('
            SELECT valeur, date_mesure, heure_mesure FROM valeurs_mesures 
            WHERE id_mesure = ? ORDER BY date_mesure DESC, heure_mesure DESC LIMIT ?
        ');
        $st->execute([$mesure['id_mesure'], $limit]);
        $valeurs = $st->fetchAll(PDO::FETCH_ASSOC);

        return ['type_mesure' => $typeMesure, 'unite' => $mesure['unite'], 'valeurs' => array_reverse($valeurs)];
    }

    public function getMesures(int $patientId): array
    {
        $st = $this->pdo->prepare('SELECT id_mesure, type_mesure, unite FROM mesures WHERE pt_id = ? ORDER BY id_mesure');
        $st->execute([$patientId]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getValeursMesure(int $mesureId, ?int $limit = null): array
    {
        $sql = 'SELECT id_val, valeur, date_mesure, heure_mesure, CONCAT(date_mesure, " ", heure_mesure) as datetime_mesure 
                FROM valeurs_mesures WHERE id_mesure = ? ORDER BY date_mesure DESC, heure_mesure DESC';
        if ($limit !== null) $sql .= ' LIMIT ' . (int)$limit;

        $st = $this->pdo->prepare($sql);
        $st->execute([$mesureId]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDernieresValeurs(int $patientId): array
    {
        $st = $this->pdo->prepare('
            SELECT m.id_mesure, m.type_mesure, m.unite, vm.valeur as derniere_valeur, vm.date_mesure as derniere_date, vm.heure_mesure as derniere_heure
            FROM mesures m INNER JOIN valeurs_mesures vm ON m.id_mesure = vm.id_mesure
            WHERE m.pt_id = ? AND (vm.date_mesure, vm.heure_mesure) = (
                SELECT date_mesure, heure_mesure FROM valeurs_mesures WHERE id_mesure = m.id_mesure ORDER BY date_mesure DESC, heure_mesure DESC LIMIT 1
            ) ORDER BY m.type_mesure
        ');
        $st->execute([$patientId]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getChartData(int $patientId, string $typeMesure, int $limit = 50): ?array
    {
        $st = $this->pdo->prepare('SELECT id_mesure, unite FROM mesures WHERE pt_id = ? AND type_mesure = ? LIMIT 1');
        $st->execute([$patientId, $typeMesure]);
        $mesure = $st->fetch(PDO::FETCH_ASSOC);
        if (!$mesure) return null;

        $st = $this->pdo->prepare('
            SELECT valeur, date_mesure, heure_mesure FROM valeurs_mesures WHERE id_mesure = ? ORDER BY date_mesure DESC, heure_mesure DESC LIMIT ?
        ');
        $st->execute([$mesure['id_mesure'], $limit]);
        $valeurs = $st->fetchAll(PDO::FETCH_ASSOC);

        return ['id_mesure' => $mesure['id_mesure'], 'type_mesure' => $typeMesure, 'unite' => $mesure['unite'], 'valeurs' => array_reverse($valeurs)];
    }

    public function getFirstPatientIdForDoctor(int $medId): ?int
    {
        $st = $this->pdo->prepare('SELECT pt_id FROM suivre WHERE med_id = ? ORDER BY pt_id LIMIT 1');
        $st->execute([$medId]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ? (int) $row['pt_id'] : null;
    }

    public function getSeuilByStatus(int $patientId, string $typeMesure, string $statut, bool $majorant): ?float
    {
        $stmt = $this->pdo->prepare("
            SELECT seuil FROM seuil_alerte sa JOIN mesures m ON m.id_mesure = sa.id_mesure
            WHERE sa.statut = :statut AND m.type_mesure = :type AND m.pt_id = :pt_id AND sa.majorant = :majorant LIMIT 1
        ");
        $stmt->execute([':statut' => $statut, ':type' => $typeMesure, ':pt_id' => $patientId, ':majorant' => $majorant ? 1 : 0]);
        $row = $stmt->fetch();
        return $row ? (float) $row['seuil'] : null;
    }
}
