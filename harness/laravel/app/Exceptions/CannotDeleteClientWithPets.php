<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

final class CannotDeleteClientWithPets extends Exception
{
    public function __construct()
    {
        parent::__construct('Cannot delete a client with pets.');
    }
}
