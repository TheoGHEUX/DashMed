<?php

namespace Domain\Repositories;

interface UserRepositoryInterface
{
    public function emailExists(string $email): bool;

    public function create(
        string $name,
        string $lastName,
        string $email,
        string $hash,
        string $sexe,
        string $specialite
    ): bool;

    public function findByEmail(string $email): ?array;

    public function findById(int $id): ?array;

    public function updatePassword(int $id, string $hash): bool;

    public function updateEmail(int $id, string $newEmail): bool;

    public function updateEmailWithVerification(int $id, string $newEmail): ?string;

    public function generateEmailVerificationToken(string $email): ?string;

    public function verifyEmailToken(string $token): bool;

    public function findByVerificationToken(string $token): ?array;
}