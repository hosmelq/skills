# Model Tests: Rounding and Minimum Casts

In-memory casts preserve a four-place increment and minimum, rounding/country/currency enum types and immutable timestamps.

```php
<?php

declare(strict_types=1);

use App\Enums\CountryCode;
use App\Enums\CurrencyCode;
use App\Enums\RoundingMode;
use App\Models\PlanRule;
use Carbon\CarbonImmutable;

it('correctly casts attributes', function (): void {
    $planRule = new PlanRule([
        'country_code' => 'US',
        'created_at' => '2026-01-15 14:39:36',
        'currency_code' => 'USD',
        'deleted_at' => '2026-01-15 14:39:36',
        'minimum_chargeable_weight' => 10,
        'rounding_increment' => 1,
        'rounding_mode' => 'up',
        'updated_at' => '2026-01-15 14:39:36',
    ]);

    expect($planRule)
        ->country_code->toBeInstanceOf(CountryCode::class)
        ->created_at->toBeInstanceOf(CarbonImmutable::class)
        ->currency_code->toBeInstanceOf(CurrencyCode::class)
        ->deleted_at->toBeInstanceOf(CarbonImmutable::class)
        ->minimum_chargeable_weight->toBe('10.0000')
        ->rounding_increment->toBe('1.0000')
        ->rounding_mode->toBeInstanceOf(RoundingMode::class)
        ->updated_at->toBeInstanceOf(CarbonImmutable::class);
});
```
