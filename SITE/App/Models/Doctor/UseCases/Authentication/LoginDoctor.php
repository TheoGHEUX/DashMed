<?php

declare(strict_types=1);

namespace App\Models\Doctor\UseCases\Authentication;

use App\Models\Doctor\Interfaces\IDoctorRepository;
use App\ValueObjects\Email;
use App\Exceptions\ValidationException;

/**
 * Use Case pour l’authentification (login d’un médecin).
 */
final class LoginDoctor
{
    private IDoctorRepository $repo;

    public function __construct(IDoctorRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * Authentifie un médecin à partir de son email et mot de passe.
     * Retourne l'entité Doctor ou null si échec.
     */
    public function execute(string $email, string $password)
    {
        // Valider le format email
        try {
            $emailVO = new Email($email);
        } catch (ValidationException $e) {
            return null;
        }

        $doctor = $this->repo->findByEmail($emailVO->getValue());

        if (!$doctor) {
            return null;
        }

        if (!password_verify($password, $doctor->getPasswordHash())) {
            return null;
        }

        return $doctor;
    }
}