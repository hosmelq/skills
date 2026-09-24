# Model Tests: Scalar and Value Object Casts

In-memory model attribute casts: backed enum types, immutable timestamps, true from a string boolean, float coordinates, arrays and a phone value object.

The fictional model exposes these attributes. Keep the exact inspected types; constructing a model requires no row, but the suite may still bootstrap Laravel.

```php
<?php

declare(strict_types=1);

use App\Enums\CountryCode;
use App\Enums\FacilityType;
use App\Models\Facility;
use Carbon\CarbonImmutable;
use Propaganistas\LaravelPhone\PhoneNumber;

it('correctly casts attributes', function (): void {
    $facility = new Facility([
        'country_code' => 'US',
        'created_at' => '2026-01-15 15:53:23',
        'deleted_at' => '2026-01-15 09:00:00',
        'is_default' => '1',
        'deactivated_at' => '2026-01-15 15:53:23',
        'latitude' => '34.052235',
        'longitude' => '-118.243683',
        'opening_hours' => [],
        'phone_number' => '+1 415 555 0110',
        'type' => 'studio',
        'updated_at' => '2026-01-15 15:53:23',
    ]);

    expect($facility)
        ->country_code->toBeInstanceOf(CountryCode::class)
        ->created_at->toBeInstanceOf(CarbonImmutable::class)
        ->deleted_at->toBeInstanceOf(CarbonImmutable::class)
        ->is_default->toBeTrue()
        ->deactivated_at->toBeInstanceOf(CarbonImmutable::class)
        ->latitude->toBeFloat()
        ->longitude->toBeFloat()
        ->opening_hours->toBeArray()
        ->phone_number->toBeInstanceOf(PhoneNumber::class)
        ->type->toBeInstanceOf(FacilityType::class)
        ->updated_at->toBeInstanceOf(CarbonImmutable::class);
});
```
