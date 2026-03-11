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

    public function __construct(string $email)
    {
        $normalized = strtolower(trim($email));

        if (!filter_var($normalized, FILTER_VALIDATE_EMAIL)) {
            throw new ValidationException(['email' => 'Format d\'email invalide.']);
        }

        $this->value = $normalized;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function equals(Email $other): bool
    {
        return $this->value === $other->value;
    }
}
