<?php

declare(strict_types=1);

namespace App\Exceptions;

/**
 * Exception pour ressource non trouvée.
 */
final class NotFoundException extends DomainException
{
    public function __construct(string $message = "Ressource introuvable", int $code = 404)
    {
        parent::__construct($message, $code);
    }
}
