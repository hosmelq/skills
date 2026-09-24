# HTTP Resource Tests: Rounding Fields

Exact resource JSON for four-place minimum/increment strings, a rounding enum backing value, country/currency codes, Sqid and timestamps.

```php
<?php

declare(strict_types=1);

use App\Enums\CountryCode;
use App\Enums\CurrencyCode;
use App\Enums\RoundingMode;
use App\Models\PlanRule;

it('formats resource correctly', function (): void {
    $planRule = PlanRule::factory()->createOne([
        'country_code' => CountryCode::UnitedStates,
        'currency_code' => CurrencyCode::CAD,
        'minimum_chargeable_weight' => 1.25,
        'name' => 'Standard allocation',
        'rounding_increment' => 0.5,
        'rounding_mode' => RoundingMode::Up,
    ]);

    $resource = json_decode($planRule->toResource()->toJson(), true);

    expect($resource)->toEqual([
        'country_code' => 'US',
        'created_at' => $planRule->created_at->toJSON(),
        'currency_code' => 'CAD',
        'id' => $planRule->sqid,
        'minimum_chargeable_weight' => '1.2500',
        'name' => 'Standard allocation',
        'rounding_increment' => '0.5000',
        'rounding_mode' => 'up',
        'updated_at' => $planRule->updated_at->toJSON(),
    ]);
});
```
