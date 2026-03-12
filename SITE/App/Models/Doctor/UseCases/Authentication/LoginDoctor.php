<?php

declare(strict_types=1);

namespace App\Models\Doctor\UseCases\Authentication;

use App\Models\Doctor\Interfaces\IDoctorRepository;
use App\ValueObjects\Email;
use App\Exceptions\ValidationException;

/**
 * Use case pour la connexion d'un médecin.
 *
 * Un use case (cas d'usage) regroupe la logique métier pour une action précise du domaine.
 * Il orchestre les appels aux repositories, validators, etc., pour réaliser une tâche métier complète.
 */
final class LoginDoctor
{
    private IDoctorRepository $repo;

    public function __construct(IDoctorRepository $repo)
    {
        $this->repo = $repo;
    }

    public function execute(string $email, string $password)
    {
        // Valider le format email
        try {
            $emailVO = new Email($email);
        } catch (ValidationException $e) {
            return null; // Format email invalide
        }

        $doctor = $this->repo->findByEmail($emailVO->getValue());

        if (!$doctor) {
            return null;
        }

        // Vérifier le mot de passe (pas de validation stricte, juste vérification du hash)
        if (!password_verify($password, $doctor->getPasswordHash())) {
            return null;
        }

        return $doctor;
    }
}
