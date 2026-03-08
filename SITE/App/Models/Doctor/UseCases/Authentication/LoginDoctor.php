<?php

declare(strict_types=1);

namespace App\Models\Doctor\UseCases\Authentication;

use App\Models\Doctor\Interfaces\IDoctorReadRepository;

class LoginDoctor
{
    private IDoctorReadRepository $readRepo;

    public function __construct(IDoctorReadRepository $readRepo)
    {
        $this->readRepo = $readRepo;
    }

    public function execute(string $email, string $password): ?array
    {
        $doctor = $this->readRepo->findByEmail($email);

        if (!$doctor) {
            return null;
        }


        $hash = $doctor['mdp'] ?? '';
        if (!password_verify($password, $hash)) {
            return null;
        }

        $isVerified = !empty($doctor['email_verified'])  == 1;

        if (!$isVerified) {

            throw new \Exception('Adresse email non vérifiée. Vérifiez vos spams.');
        }

        return $doctor;
    }
}