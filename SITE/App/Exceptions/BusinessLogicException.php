<?php

declare(strict_types=1);

namespace App\Exceptions;

/**
 * Exception levée lors d'une erreur de logique métier
 */
final class BusinessLogicException extends DomainException
{
    public function __construct(string $message = "Erreur de logique métier", int $code = 422)
    {
        parent::__construct($message, $code);
    }
}
