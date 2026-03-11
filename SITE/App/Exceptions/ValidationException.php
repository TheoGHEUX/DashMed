<?php

declare(strict_types=1);

namespace App\Exceptions;

/**
 * Exception levée lors d'erreur de validation des données
 */
class ValidationException extends DomainException
{
    private array $errors;

    public function __construct(array $errors, string $message = "Erreur de validation", int $code = 400)
    {
        parent::__construct($message, $code);
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
