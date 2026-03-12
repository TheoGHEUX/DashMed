<?php

declare(strict_types=1);

namespace App\Exceptions;

/**
 * Exception pour accès refusé à une ressource.
 */
final class AuthorizationException extends DomainException
{
    public function __construct(string $message = "Accès refusé", int $code = 403)
    {
        parent::__construct($message, $code);
    }
}
