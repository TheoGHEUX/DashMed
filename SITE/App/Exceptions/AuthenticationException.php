<?php

declare(strict_types=1);

namespace App\Exceptions;

/**
 * Exception pour échec d'authentification.
 */
final class AuthenticationException extends DomainException
{
    public function __construct(string $message = "Authentification échouée", int $code = 401)
    {
        parent::__construct($message, $code);
    }
}
