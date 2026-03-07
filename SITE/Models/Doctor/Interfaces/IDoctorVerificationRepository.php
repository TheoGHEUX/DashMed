<?php

declare(strict_types=1);

namespace Models\Doctor\Interfaces;

use Models\Doctor\Entities\Doctor;

interface IDoctorVerificationRepository
{
    public function findByVerificationToken(string $token): ?Doctor;
    public function setVerificationToken(string $email, string $token, string $expires): bool;
    public function verifyEmailToken(string $token): bool;
}