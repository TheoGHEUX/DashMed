<?php

namespace Models\Entities;

class ConsoleLog
{
    private int $id;
    private int $medId;
    private string $typeAction;
    private ?int $ptId;
    private ?int $mesureId;
    private string $date;
    private string $heure;

    public function __construct(array $data)
    {
        $this->id = (int) ($data['log_id'] ?? 0);
        $this->medId = (int) ($data['med_id'] ?? 0);
        $this->typeAction = $data['type_action'] ?? '';
        $this->ptId = isset($data['pt_id']) ? (int) $data['pt_id'] : null;
        $this->mesureId = isset($data['id_mesure']) ? (int) $data['id_mesure'] : null;
        $this->date = $data['date_action'] ?? '';
        $this->heure = $data['heure_action'] ?? '';
    }

}