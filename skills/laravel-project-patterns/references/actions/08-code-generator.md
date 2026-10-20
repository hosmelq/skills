# Actions: Code Generator

Generate a human-readable code that is unique within the tenant, retrying with the injected Nano ID client and failing after a bounded number of attempts.

- Compare normalized codes so codes differing only in formatting collide.
- Mock the Nano ID client in tests; the attempt limit is a public constant because tests use it.

```php
<?php

declare(strict_types=1);

namespace App\Enums;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

/**
 * @method static string Alphanumeric()
 * @method static string Letters()
 * @method static string Numbers()
 */
enum NanoIDAlphabet: string
{
    use InvokableCases;
    use Values;

    case Alphanumeric = 'abcdefghijklmnopqrstuvwxyz0123456789';
    case Letters = 'abcdefghijklmnopqrstuvwxyz';
    case Numbers = '0123456789';
}
```

```php
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
```

```php
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
        return $clinic->rooms()->where('normalized_code', Room::normalizeCode($code))->exists();
    }
}
```

```php
<?php

declare(strict_types=1);

use App\Actions\GenerateRoomCode;
use App\Exceptions\CannotGenerateRoomCode;
use App\Models\Room;
use Hidehalo\Nanoid\Client;
use Mockery\MockInterface;

it('throws when unable to generate a unique room code after max attempts', function (): void {
    $room = Room::factory()->createOne([
        'code' => 'RM-004992',
        'normalized_code' => 'RM004992',
    ]);

    $this->mock(Client::class, function (MockInterface $mock): void {
        $mock->shouldReceive('formattedId')
            ->times(GenerateRoomCode::MAX_RETRY_ATTEMPTS)
            ->andReturn('004992');
    });

    resolve(GenerateRoomCode::class)->handle($room->clinic);
})->throws(
    CannotGenerateRoomCode::class,
    'Unable to generate unique room code after 20 attempts.',
);

it('retries until a unique normalized room code is found', function (): void {
    $room = Room::factory()->createOne([
        'code' => 'RM-004992',
        'normalized_code' => 'RM004992',
    ]);

    $this->mock(Client::class, function (MockInterface $mock): void {
        $mock->shouldReceive('formattedId')->andReturnValues(['004992', '004993']);
    });

    $code = resolve(GenerateRoomCode::class)->handle($room->clinic);

    expect($code)->toBe('RM-004993');
});
```
