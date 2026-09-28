# Support: Canonical Sqids

Encode one integer. Accept a decoded value only when exactly one number is returned and re-encoding matches the input byte for byte. Return `null` otherwise; decoded `0` remains valid. The configured `SqidsInterface` implementation supplies the alphabet and minimum length.

```php
<?php

declare(strict_types=1);

namespace App\Support;

use Sqids\SqidsInterface;

class Sqid
{
    public function __construct(private readonly SqidsInterface $sqids)
    {
    }

    public function decode(string $sqid): null|int
    {
        $numbers = $this->sqids->decode($sqid);

        if (count($numbers) !== 1 || $this->encode($numbers[0]) !== $sqid) {
            return null;
        }

        return $numbers[0];
    }

    public function encode(int $id): string
    {
        return $this->sqids->encode([$id]);
    }
}
```

Bind the interface through this deferred provider and register it in `bootstrap/providers.php`. Set `identifiers.sqids.min_length` to the inspected integer configuration (`10` in this example); keep that setting consistent with callers.

```php
<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Override;
use Sqids\Sqids;
use Sqids\SqidsInterface;

class SqidsServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * @return list<class-string>
     */
    #[Override]
    public function provides(): array
    {
        return [SqidsInterface::class];
    }

    #[Override]
    public function register(): void
    {
        $this->app->singleton(fn (): SqidsInterface => new Sqids(
            minLength: Config::integer('identifiers.sqids.min_length'),
        ));
    }
}
```
