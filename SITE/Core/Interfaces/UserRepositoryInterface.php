<?php

namespace Core\Interfaces;

use Models\Entities\User;

/**
 * Interface Repository Utilisateur
 *
 * Définit le contrat que tout système de stockage d'utilisateurs doit respecter.
 * Cela permet de découpler le code métier de l'implémentation SQL concrète.
 */
interface UserRepositoryInterface
{
    // Méthodes de lecture (Read)
    public function findByEmail(string $email): ?User;
    public function findById(int $id): ?User;
    public function findByVerificationToken(string $token): ?User;
    public function emailExists(string $email): bool;

    // Méthodes d'écriture (Write)
    public function create(array $data): bool;
    public function updatePassword(int $id, string $hash): bool;
    public function updateEmail(int $id, string $email): bool;

    // Méthodes spécifiques à la vérification d'email
    public function setVerificationToken(string $email, string $token, string $expires): bool;
    public function verifyEmailToken(string $token): bool;
}