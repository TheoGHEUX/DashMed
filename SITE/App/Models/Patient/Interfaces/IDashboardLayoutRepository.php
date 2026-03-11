<?php

declare(strict_types=1);

namespace App\Models\Patient\Interfaces;

/**
 * Interface pour les opérations de gestion du layout du dashboard
 * Respecte l'ISP en se concentrant uniquement sur les layouts
 */
interface IDashboardLayoutRepository
{
    /**
     * Récupère la configuration du layout pour un patient et un médecin
     */
    public function getDashboardLayout(int $patientId, int $medId): ?array;

    /**
     * Sauvegarde la configuration du layout
     */
    public function saveDashboardLayout(int $patientId, int $medId, array $config): bool;
}
