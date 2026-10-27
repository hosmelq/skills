<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\NanoIDAlphabet;
use App\Exceptions\CannotGenerateOneTimePasswordCode;
use App\Models\OneTimePassword;
use Hidehalo\Nanoid\Client;
use Illuminate\Support\Facades\DB;

final readonly class GenerateOneTimePasswordCode
{
    public const int EXPIRATION_MINUTES = 30;

    public const int MAX_RETRY_ATTEMPTS = 20;

    public function __construct(private Client $client) {}

    public function handle(string $email): OneTimePassword
    {
        return DB::transaction(function () use ($email): OneTimePassword {
            $this->deleteExistingCodes($email);

            return OneTimePassword::query()->create([
                'code' => $this->generateUniqueCode(),
                'email' => $email,
                'expires_at' => now()->addMinutes(self::EXPIRATION_MINUTES),
            ]);
        });
    }

    private function codeExists(string $code): bool
    {
        return OneTimePassword::query()->where('code', $code)->exists();
    }

    private function deleteExistingCodes(string $email): void
    {
        OneTimePassword::query()
            ->where('email', $email)
            ->whereNull('used_at')
            ->delete();
    }

    private function generateUniqueCode(): string
    {
        $attempts = 0;

        while ($attempts < self::MAX_RETRY_ATTEMPTS) {
            $code = $this->client->formattedId(NanoIDAlphabet::Numbers(), 6);

            ++$attempts;

            if (! $this->codeExists($code)) {
                return $code;
            }
        }

        throw CannotGenerateOneTimePasswordCode::maxAttempts($attempts);
    }
}
