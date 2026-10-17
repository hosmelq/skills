# Models: Casts

Cast each model column whose PHP value differs from the raw database value, including phone numbers stored in E.164, and test every cast with raw database values.

- Sort `casts()` keys alphabetically. Eloquent timestamps need no cast, but the test still asserts them.
- `decimal:N` returns a string: type it `string` in `@property-read` and compare it with strings.
- Cast phone numbers stored in E.164 with `E164PhoneNumberCast::class` and no country.
- Building models with attributes relies on unguarded models; use `forceFill()` when the project guards them.

```php
<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CountryCode;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Override;
use Propaganistas\LaravelPhone\Casts\E164PhoneNumberCast;
use Propaganistas\LaravelPhone\PhoneNumber;

/**
 * @property-read bool $accepts_emergencies
 * @property-read string $consultation_fee
 * @property-read CountryCode $country_code
 * @property-read null|float $latitude
 * @property-read null|array<string, mixed> $opening_hours
 * @property-read null|PhoneNumber $phone_number
 * @property-read null|CarbonImmutable $verified_at
 * @property-read int $visit_duration_minutes
 */
final class Clinic extends Model
{
    #[Override]
    protected function casts(): array
    {
        return [
            'accepts_emergencies' => 'boolean',
            'consultation_fee' => 'decimal:2',
            'country_code' => CountryCode::class,
            'latitude' => 'float',
            'opening_hours' => 'array',
            'phone_number' => E164PhoneNumberCast::class,
            'verified_at' => 'datetime',
            'visit_duration_minutes' => 'integer',
        ];
    }
}
```

```php
<?php

declare(strict_types=1);

use App\Enums\CountryCode;
use App\Models\Clinic;
use Carbon\CarbonImmutable;
use Propaganistas\LaravelPhone\PhoneNumber;

it('correctly casts attributes', function (): void {
    $clinic = new Clinic([
        'accepts_emergencies' => '1',
        'consultation_fee' => '25',
        'country_code' => 'NI',
        'created_at' => '2026-05-02 15:25:17',
        'latitude' => '12.1364',
        'opening_hours' => [],
        'phone_number' => '+50588888888',
        'updated_at' => '2026-05-02 15:25:17',
        'verified_at' => '2026-05-02 15:25:17',
        'visit_duration_minutes' => '30',
    ]);

    expect($clinic)
        ->accepts_emergencies->toBeTrue()
        ->consultation_fee->toBe('25.00')
        ->country_code->toBe(CountryCode::Nicaragua)
        ->created_at->toBeInstanceOf(CarbonImmutable::class)
        ->latitude->toBeFloat()
        ->opening_hours->toBeArray()
        ->phone_number->toBeInstanceOf(PhoneNumber::class)
        ->updated_at->toBeInstanceOf(CarbonImmutable::class)
        ->verified_at->toBeInstanceOf(CarbonImmutable::class)
        ->visit_duration_minutes->toBe(30);
});
```
