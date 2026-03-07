<?php

declare(strict_types=1);

namespace Models\Doctor\UseCases\Authentication;

use Models\Doctor\Interfaces\IDoctorReadRepository;
use Models\Doctor\Entities\Doctor;

class LoginDoctor
{
    private IDoctorReadRepository $repository;

    public function __construct(IDoctorReadRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Tente de connecter un médecin.
     * @return Doctor|null L'objet médecin si succès, null si échec.
     * @throws \Exception Si l'email n'est pas vérifié.
     */
    public function execute(string $email, string $password): ?Doctor
    {
        // 1. Chercher l'utilisateur
        $user = $this->repository->findByEmail($email);

        if (!$user) {
            return null; // Utilisateur introuvable
        }

        // 2. Vérifier le mot de passe hashé
        if (!password_verify($password, $user->getPasswordHash())) {
            return null; // Mot de passe incorrect
        }

        // 3. Vérifier si l'email est validé
        if (!$user->isEmailVerified()) {
            throw new \Exception("Veuillez vérifier votre adresse email avant de vous connecter.");
        }

        return $user;
    }
}