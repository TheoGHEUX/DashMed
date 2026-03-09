<?php

declare(strict_types=1);

namespace App\Models\Doctor\UseCases\Authentication;

use App\Models\Doctor\Interfaces\IDoctorRepository;

class LoginDoctor
{
    private IDoctorRepository $repo;

    public function __construct(IDoctorRepository $repo)
    {
        $this->repo = $repo;
    }

    public function execute(string $email, string $password)
    {
        $doctor = $this->repo->findByEmail($email);

        if (!$doctor) {
            return null;
        }

        if (!password_verify($password, $doctor->getPasswordHash())) {
            return null;
        }

        return $doctor;
    }
}