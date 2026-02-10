<?php
namespace Models;

use Infrastructure\Persistence\SqlHistoriqueConsoleRepository;

final class HistoriqueConsole
{
    private static function getRepo(): SqlHistoriqueConsoleRepository
    {
        return new SqlHistoriqueConsoleRepository();
    }

    public static function log(int $medId, string $typeAction, ?int $ptId = null, ?int $idMesure = null): bool
    {
        return self::getRepo()->log($medId, $typeAction, $ptId, $idMesure);
    }

    public static function logGraphiqueAjouter(int $medId, ?int $ptId = null, ?int $idMesure = null): bool
    {
        return self::log($medId, 'ajouter', $ptId, $idMesure);
    }

    public static function logGraphiqueSupprimer(int $medId, ?int $ptId = null, ?int $idMesure = null): bool
    {
        return self::log($medId, 'supprimer', $ptId, $idMesure);
    }

    public static function logGraphiqueReduire(int $medId, ?int $ptId = null, ?int $idMesure = null): bool
    {
        return self::log($medId, 'réduire', $ptId, $idMesure);
    }

    public static function logGraphiqueAgrandir(int $medId, ?int $ptId = null, ?int $idMesure = null): bool
    {
        return self::log($medId, 'agrandir', $ptId, $idMesure);
    }

    public static function getHistoryByMedId(int $medId, int $limit = 100): array
    {
        return self::getRepo()->getHistoryByMedId($medId, $limit);
    }
}