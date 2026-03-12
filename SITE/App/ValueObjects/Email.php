<?php

declare(strict_types=1);

namespace App\ValueObjects;

use App\Exceptions\ValidationException;

/**
 * Value Object pour représenter une adresse email
 * Immuable et validé à la construction
 */
final class Email
{
    private string $value;

    /**
     * Construit un email et valide son format.
     */
    public function __construct(string $email)
    {
        $normalized = strtolower(trim($email));

        if (!filter_var($normalized, FILTER_VALIDATE_EMAIL)) {
            throw new ValidationException(['email' => 'Format d\'email invalide.']);
        }

        $this->value = $normalized;
    }

    /**
     * Retourne la valeur de l'email.
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Retourne l'email sous forme de chaîne.
     */
    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * Compare deux objets Email pour l'égalité.
     */
    public function equals(Email $other): bool
    {
        return $this->value === $other->value;
    }
}
