<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

/**
 * Exception de base pour toutes les exceptions métier de l'application
 */
abstract class DomainException extends Exception
{
}
