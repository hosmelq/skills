# Actions: Generate a One-Time Code

Delete unused codes for an email, generate a globally unused numeric candidate with bounded retries, then persist expiry inside one transaction. Used codes remain and participate in collision checks.

The existence probe is separate from the database uniqueness constraint. This action does not implement an insert-conflict retry.

```php
<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\NanoIDAlphabet;
use App\Exceptions\CannotGenerateOneTimePasswordCode;
use App\Models\OneTimePassword;
use Hidehalo\Nanoid\Client;
use Illuminate\Support\Facades\DB;

class GenerateOneTimePasswordCode
{
    public const int CODE_LENGTH = 6;

    public const int EXPIRATION_MINUTES = 30;

    public const int MAX_RETRY_ATTEMPTS = 20;

    public function __construct(private readonly Client $client)
    {
    }

    public function handle(string $email): OneTimePassword
    {
        return DB::transaction(function () use ($email): OneTimePassword {
            $this->deleteExistingCodes($email);

            $code = $this->generateUniqueCode();

            return OneTimePassword::query()->create([
                'code' => $code,
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

    private function generateCode(): string
    {
        return $this->client->formattedId(NanoIDAlphabet::Numbers(), self::CODE_LENGTH);
    }

    private function generateUniqueCode(): string
    {
        $attempts = 0;

        while ($attempts < self::MAX_RETRY_ATTEMPTS) {
            $code = $this->generateCode();

            ++$attempts;

            if (! $this->codeExists($code)) {
                return $code;
            }
        }

        throw CannotGenerateOneTimePasswordCode::maxAttempts($attempts);
    }
}
```
