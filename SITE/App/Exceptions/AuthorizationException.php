<?php

declare(strict_types=1);

namespace App\Exceptions;

/**
 * Exception levée quand l'accès à une ressource est refusé
 */
class AuthorizationException extends DomainException
{
    public function __construct(string $message = "Accès refusé", int $code = 403)
    {
        parent::__construct($message, $code);
    }
}
