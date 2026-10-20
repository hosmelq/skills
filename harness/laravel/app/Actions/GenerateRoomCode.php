<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\NanoIDAlphabet;
use App\Exceptions\CannotGenerateRoomCode;
use App\Models\Clinic;
use App\Models\Room;
use Hidehalo\Nanoid\Client;

final readonly class GenerateRoomCode
{
    public const int MAX_RETRY_ATTEMPTS = 20;

    public function __construct(private Client $client) {}

    public function handle(Clinic $clinic): string
    {
        $attempts = 0;

        while ($attempts < self::MAX_RETRY_ATTEMPTS) {
            $code = 'RM-'.$this->client->formattedId(NanoIDAlphabet::Numbers(), 6);

            ++$attempts;

            if (! $this->codeExists($clinic, $code)) {
                return $code;
            }
        }

        throw CannotGenerateRoomCode::maxAttempts($attempts);
    }

    private function codeExists(Clinic $clinic, string $code): bool
    {
        return $clinic->rooms()
            ->where('normalized_code', Room::normalizeCode($code))
            ->exists();
    }
}
