<?php

declare(strict_types=1);

namespace Models\Doctor\Interfaces;

interface IDoctorWriteRepository
{
    public function create(array $data): bool;
    public function updatePassword(int $id, string $hash): bool;
    public function updateEmail(int $id, string $email): bool;
}