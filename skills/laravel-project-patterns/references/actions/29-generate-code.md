# Actions: Generate a Scoped Code

Injected NanoID generation with tenant alphabet, length and optional prefix, bounded retries and normalized collision checks. Return an unreserved candidate or throw after exhausting attempts.

```php
<?php

declare(strict_types=1);

namespace App\Actions;

use App\Exceptions\CannotGenerateCabinetCode;
use App\Models\Cabinet;
use App\Models\Team;
use Hidehalo\Nanoid\Client;

class GenerateCabinetCode
{
    public const int MAX_RETRY_ATTEMPTS = 20;

    public function __construct(private readonly Client $client)
    {
    }

    public function handle(Team $team): string
    {
        $attempts = 0;

        while ($attempts < self::MAX_RETRY_ATTEMPTS) {
            $code = $this->generateCode($team);

            ++$attempts;

            if (! $this->codeExists($team, $code)) {
                return $code;
            }
        }

        throw CannotGenerateCabinetCode::maxAttempts($attempts);
    }

    private function codeExists(Team $team, string $code): bool
    {
        return Cabinet::query()
            ->where('team_id', $team->id)
            ->where('normalized_code', Cabinet::normalizeCode($code))
            ->exists();
    }

    private function generateCode(Team $team): string
    {
        $code = $this->client->formattedId(
            $team->code_format_alphabet_type->alphabet(),
            $team->code_format_length,
        );

        return ($team->code_format_prefix ?? '').$code;
    }
}
```
