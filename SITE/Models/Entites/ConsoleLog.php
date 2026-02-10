<?php

namespace Models\Entities;

class ConsoleLog
{
    private int $id;
    private string $message;
    private string $level; // INFO, WARNING, ERROR
    private string $date;

    public function __construct(int $id, string $message, string $level, string $date)
    {
        $this->id = $id;
        $this->message = $message;
        $this->level = $level;
        $this->date = $date;
    }

    public function getMessage(): string { return $this->message; }
    public function getLevel(): string { return $this->level; }
    public function getDate(): string { return $this->date; }
}
