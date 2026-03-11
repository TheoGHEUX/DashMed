<?php

declare(strict_types=1);

namespace App\ValueObjects;

use App\Exceptions\ValidationException;

/**
 * Value Object pour représenter un mot de passe
 * Valide la force du mot de passe
 */
final class Password
{
    private const MIN_LENGTH = 8;

    private string $value;

    public function __construct(string $password)
    {
        if (strlen($password) < self::MIN_LENGTH) {
            throw new ValidationException(
                ['password' => 'Le mot de passe doit contenir au moins ' . self::MIN_LENGTH . ' caractères.']
            );
        }

        if (!preg_match('/[A-Z]/', $password)) {
            throw new ValidationException(
                ['password' => 'Le mot de passe doit contenir au moins une majuscule.']
            );
        }

        if (!preg_match('/[a-z]/', $password)) {
            throw new ValidationException(
                ['password' => 'Le mot de passe doit contenir au moins une minuscule.']
            );
        }

        if (!preg_match('/[0-9]/', $password)) {
            throw new ValidationException(
                ['password' => 'Le mot de passe doit contenir au moins un chiffre.']
            );
        }

        $this->value = $password;
    }

    public function hash(): string
    {
        return password_hash($this->value, PASSWORD_DEFAULT);
    }

    public function verify(string $hash): bool
    {
        return password_verify($this->value, $hash);
    }
}
