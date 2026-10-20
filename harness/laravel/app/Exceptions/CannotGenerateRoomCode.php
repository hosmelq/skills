<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

final class CannotGenerateRoomCode extends Exception
{
    public static function maxAttempts(int $attempts): self
    {
        return new self(
            sprintf('Unable to generate unique room code after %d attempts.', $attempts),
        );
    }
}
