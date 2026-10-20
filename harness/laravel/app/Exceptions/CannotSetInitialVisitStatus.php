<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

final class CannotSetInitialVisitStatus extends Exception
{
    public static function becauseItIsNotActiveCheckedIn(): self
    {
        return new self('Only an active checked-in visit status can be initial.');
    }
}
