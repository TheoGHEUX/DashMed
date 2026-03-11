<?php

declare(strict_types=1);

namespace Core\Services;

/**
 * Service de génération de tokens sécurisés
 * Centralise la création de tokens pour éviter la duplication
 */
final class TokenGenerator
{
    /**
     * Génère un token sécurisé aléatoire
     *
     * @param int $length Longueur du token en bytes (32 par défaut = 64 caractères hex)
     * @return string Token hexadécimal
     * @throws \Exception Si random_bytes échoue
     */
    public static function generate(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }

    /**
     * Génère un token avec une date d'expiration
     *
     * @param int $length Longueur du token en bytes
     * @param string $expiresIn Format compatible avec strtotime (ex: '+24 hours', '+1 day')
     * @return array ['token' => string, 'expires' => string (Y-m-d H:i:s)]
     * @throws \Exception Si random_bytes échoue
     */
    public static function generateWithExpiry(int $length = 32, string $expiresIn = '+24 hours'): array
    {
        return [
            'token' => self::generate($length),
            'expires' => date('Y-m-d H:i:s', strtotime($expiresIn))
        ];
    }

    /**
     * Hash un token (pour stockage sécurisé en base)
     *
     * @param string $token Token en clair
     * @return string Hash du token
     */
    public static function hash(string $token): string
    {
        return hash('sha256', $token);
    }

    /**
     * Vérifie si un token correspond à un hash
     *
     * @param string $token Token en clair
     * @param string $hash Hash stocké en base
     * @return bool
     */
    public static function verify(string $token, string $hash): bool
    {
        return hash_equals($hash, self::hash($token));
    }
}
