<?php
namespace Models;

use Infrastructure\Persistence\SqlUserRepository;

final class User
{
    private static function getRepo(): SqlUserRepository
    {
        return new SqlUserRepository();
    }

    public static function emailExists(string $email): bool
    {
        return self::getRepo()->emailExists($email);
    }

    public static function create($name, $lastName, $email, $hash, $sexe, $specialite): bool
    {
        return self::getRepo()->create($name, $lastName, $email, $hash, $sexe, $specialite);
    }

    public static function findByEmail(string $email): ?array
    {
        return self::getRepo()->findByEmail($email);
    }

    public static function findById(int $id): ?array
    {
        return self::getRepo()->findById($id);
    }

    public static function updatePassword(int $id, string $hash): bool
    {
        return self::getRepo()->updatePassword($id, $hash);
    }

    public static function updateEmail(int $id, string $newEmail): bool
    {
        return self::getRepo()->updateEmail($id, $newEmail);
    }

    public static function updateEmailWithVerification(int $id, string $newEmail): ?string
    {
        return self::getRepo()->updateEmailWithVerification($id, $newEmail);
    }

    public static function generateEmailVerificationToken(string $email): ?string
    {
        return self::getRepo()->generateEmailVerificationToken($email);
    }

    public static function verifyEmailToken(string $token): bool
    {
        return self::getRepo()->verifyEmailToken($token);
    }

    public static function findByVerificationToken(string $token): ?array
    {
        return self::getRepo()->findByVerificationToken($token);
    }
}