<?php

declare(strict_types=1);

namespace App\Models\Doctor\Interfaces;

use App\Models\Doctor\Entities\Doctor;

interface IDoctorReadRepository
{
    public function findByEmail(string $email): ?Doctor;
    public function findById(int $id): ?Doctor;
    public function emailExists(string $email): bool;
}