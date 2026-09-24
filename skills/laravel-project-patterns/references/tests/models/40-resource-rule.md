# Model Tests: Rule Resource and Loaded Parent

Exact resource JSON for decimal minimum/increment, rounding enum, geographic and currency codes; separately compare the full loaded parent resource.

```php
<?php

declare(strict_types=1);

use App\Enums\CountryCode;
use App\Enums\CurrencyCode;
use App\Enums\RoundingMode;
use App\Models\PlanRule;

it('formats resource correctly', function (): void {
    $planRule = PlanRule::factory()->createOne([
        'rounding_increment' => 0.5,
        'rounding_mode' => RoundingMode::Up,
        'country_code' => CountryCode::UnitedStates,
        'currency_code' => CurrencyCode::CAD,
        'minimum_chargeable_weight' => 1.25,
        'name' => 'Standard allocation',
    ]);

    $resource = json_decode($planRule->toResource()->toJson(), true);

    expect($resource)->toEqual([
        'rounding_increment' => '0.5000',
        'rounding_mode' => 'up',
        'country_code' => 'US',
        'created_at' => $planRule->created_at->toJSON(),
        'currency_code' => 'CAD',
        'id' => $planRule->public_id,
        'minimum_chargeable_weight' => '1.2500',
        'name' => 'Standard allocation',
        'updated_at' => $planRule->updated_at->toJSON(),
    ]);
});

it('includes loaded relationship resources', function (): void {
    $planRule = PlanRule::factory()->createOne();

    $servicePlanResource = json_decode(
        $planRule->servicePlan->toResource()->toJson(),
        true,
    );

    $resource = json_decode($planRule->toResource()->toJson(), true);

    expect($resource)->service_plan->toEqual($servicePlanResource);
});
```
