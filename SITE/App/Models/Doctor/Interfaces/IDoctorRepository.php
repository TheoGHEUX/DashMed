<?php

declare(strict_types=1);

namespace App\Models\Doctor\Interfaces;

use App\Models\Doctor\Entities\Doctor;

interface IDoctorRepository
{
    public function findByEmail(string $email): ?Doctor;
    public function findById(int $id): ?Doctor;
    public function emailExists(string $email): bool;
    public function create(array $data): bool;
    public function updatePassword(int $id, string $hash): bool;
    public function updateEmail(int $id, string $email): bool;
}
