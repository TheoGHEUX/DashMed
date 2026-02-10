<?php
namespace Models;

use Infrastructure\Persistence\SqlPatientRepository;

final class Patient
{
    private static function getRepo(): SqlPatientRepository
    {
        return new SqlPatientRepository();
    }

    public static function findById(int $id): ?array
    {
        return self::getRepo()->findById($id);
    }

    public static function getPatientsForDoctor(int $doctorId): array
    {
        return self::getRepo()->getPatientsForDoctor($doctorId);
    }

    public static function findByIdForDoctor(int $patientId, int $medId): ?array
    {
        return self::getRepo()->findByIdForDoctor($patientId, $medId);
    }

    public static function getChartDataForDoctor(int $medId, int $patientId, string $typeMesure, int $limit = 50): ?array
    {
        return self::getRepo()->getChartDataForDoctor($medId, $patientId, $typeMesure, $limit);
    }

    public static function getMesures(int $patientId): array
    {
        return self::getRepo()->getMesures($patientId);
    }

    public static function getValeursMesure(int $mesureId, ?int $limit = null): array
    {
        return self::getRepo()->getValeursMesure($mesureId, $limit);
    }

    public static function getDernieresValeurs(int $patientId): array
    {
        return self::getRepo()->getDernieresValeurs($patientId);
    }

    public static function getChartData(int $patientId, string $typeMesure, int $limit = 50): ?array
    {
        return self::getRepo()->getChartData($patientId, $typeMesure, $limit);
    }

    public static function getFirstPatientIdForDoctor(int $medId): ?int
    {
        return self::getRepo()->getFirstPatientIdForDoctor($medId);
    }

    public static function getSeuilByStatus(int $patientId, string $typeMesure, string $statut, bool $majorant): ?float
    {
        return self::getRepo()->getSeuilByStatus($patientId, $typeMesure, $statut, $majorant);
    }

    // Méthodes utilitaires statiques (ne dépendent pas de la BDD, peuvent rester ici ou aller dans un Service)
    public static function normalizeValue(float $value, float $min, float $max): float
    {
        if ($max === $min) return 0.5;
        return max(0, min(1, ($value - $min) / ($max - $min)));
    }

    public static function prepareChartValues(array $valeurs, float $min, float $max): array
    {
        return array_map(function ($v) use ($min, $max) {
            return self::normalizeValue((float)$v['valeur'], $min, $max);
        }, $valeurs);
    }
}