# Models: Lookup Models on Another Connection

Read reference data that lives in a separate database connection through read-only models bound to that connection with `#[Connection]`.

- Read-only lookup models omit the traits and contracts that exist to create, expose or audit application records.
- Relations between lookup models stay on the same connection; do not join them with application tables.
- Document every column with `@property-read` in the external dataset's own column order, then relations alphabetically.
- Lookup models need no tests unless they add casts or behavior.

```php
<?php

declare(strict_types=1);

namespace App\Models\World;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read int $id
 * @property-read string $name
 * @property-read null|string $iso2
 * @property-read null|string $phonecode
 * @property-read CarbonImmutable $created_at
 * @property-read CarbonImmutable $updated_at
 * @property-read Collection<int, City> $cities
 * @property-read Collection<int, State> $states
 */
#[Connection('world')]
final class Country extends Model
{
    /**
     * @return HasMany<City, $this>
     */
    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }

    /**
     * @return HasMany<State, $this>
     */
    public function states(): HasMany
    {
        return $this->hasMany(State::class);
    }
}
```
