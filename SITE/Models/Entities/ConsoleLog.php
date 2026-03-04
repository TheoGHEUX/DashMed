<?php

namespace Models\Entities;

/**
 * Entité Log Console
 *
 *  Log pour les actions effectuées dans le tableau de bord.
 *
 * Stocke les détails d'une action : qui a fait l'action (médecin), quel type d'action,
 * sur quel patient et quelle mesure, ainsi que l'heure.
 *
 * @package Models\Entities
 */
class ConsoleLog
{
    private int $id;
    private int $medId;
    private string $typeAction;
    private int $typeActionId;
    private ?int $ptId;
    private ?int $mesureId;
    private string $date;
    private string $heure;

    /**
     *
     * Mappe les clés de la base de données vers les propriétés typées de l'objet.
     *
     * @param array $data généralement une ligne de BD
     */
    public function __construct(array $data)
    {
        $this->id = (int) ($data['log_id'] ?? 0);
        $this->medId = (int) ($data['med_id'] ?? 0);
        $this->typeAction = $data['type_action'] ?? '';
        $this->typeActionId = (int) ($data['type_action_id'] ?? 0);
        $this->ptId = isset($data['pt_id']) ? (int) $data['pt_id'] : null;
        $this->mesureId = isset($data['id_mesure']) ? (int) $data['id_mesure'] : null;
        $this->date = $data['date_action'] ?? '';
        $this->heure = $data['heure_action'] ?? '';
    }

    // --- Méthodes pour récupérer les infos (Getters) ---

    /**
     * @return int Identifiant du log
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return int Identifiant du médecin
     */
    public function getMedId(): int
    {
        return $this->medId;
    }

    /**
     * @return string Type d'action
     */
    public function getTypeAction(): string
    {
        return $this->typeAction;
    }

    /**
     * @return int Identifiant numérique du type d'action (0-3)
     */
    public function getTypeActionId(): int
    {
        return $this->typeActionId;
    }

    /**
     * @return int|null Identifiant du patient ou null
     */
    public function getPtId(): ?int
    {
        return $this->ptId;
    }

    /**
     * @return int|null Identifiant de la mesure ou null
     */
    public function getMesureId(): ?int
    {
        return $this->mesureId;
    }

    /**
     * @return string Date de l'action
     */
    public function getDate(): string
    {
        return $this->date;
    }

    /**
     * @return string Heure de l'action
     */
    public function getHeure(): string
    {
        return $this->heure;
    }
}
