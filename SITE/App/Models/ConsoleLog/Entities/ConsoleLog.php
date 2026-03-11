<?php

declare(strict_types=1);

namespace App\Models\ConsoleLog\Entities;

final class ConsoleLog
{
    private int $logId;
    private int $medId;
    private string $typeAction;
    private int $typeActionId;
    private ?int $ptId;
    private ?int $idMesure;
    private string $dateAction;
    private string $heureAction;

    private ?string $nomMesure;

    public function __construct(array $data)
    {
        $this->logId = (int)$data['log_id'];
        $this->medId = (int)$data['med_id'];
        $this->typeAction = $data['type_action'];
        $this->typeActionId = (int)$data['type_action_id'];

        $this->ptId = isset($data['pt_id']) ? (int)$data['pt_id'] : null;
        $this->idMesure = isset($data['id_mesure']) ? (int)$data['id_mesure'] : null;

        $this->dateAction = $data['date_action'];
        $this->heureAction = $data['heure_action'];

        $this->nomMesure = $data['nom_mesure'] ?? $data['type_mesure'] ?? null;
    }

    // Getters
    public function getLogId(): int
    {
        return $this->logId;
    }
    public function getMedId(): int
    {
        return $this->medId;
    }
    public function getTypeAction(): string
    {
        return $this->typeAction;
    }
    public function getTypeActionId(): int
    {
        return $this->typeActionId;
    }
    public function getPtId(): ?int
    {
        return $this->ptId;
    }
    public function getIdMesure(): ?int
    {
        return $this->idMesure;
    }
    public function getDateAction(): string
    {
        return $this->dateAction;
    }
    public function getHeureAction(): string
    {
        return $this->heureAction;
    }
    public function getNomMesure(): ?string
    {
        return $this->nomMesure;
    }
}
