# Action Tests: Create Configured Fields

Integration tests for creating a parent with duration bounds and measurement-unit enums: full supported input and required-only defaults, persisted ownership and nullable fields.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Actions\ServicePlans\CreateServicePlan;
use App\Actions\ServicePlans\Inputs\CreateServicePlanInput;
use App\Enums\TransitTimeUnit;
use App\Enums\WeightUnit;
use App\Models\ServicePlan;
use App\Models\Team;

it('creates a record', function (): void {
    $team = Team::factory()->createOne();

    $servicePlan = resolve(CreateServicePlan::class)->handle(
        $team,
        CreateServicePlanInput::from([
            'description' => 'Standard plan option',
            'estimated_transit_time_unit' => TransitTimeUnit::Days(),
            'maximum_estimated_transit_time' => 6,
            'minimum_estimated_transit_time' => 4,
            'name' => 'Standard plan',
            'weight_unit' => WeightUnit::Pounds(),
        ]),
    );

    expect($servicePlan)->toBeInstanceOf(ServicePlan::class);

    assertDatabaseHas(ServicePlan::class, [
        'id' => $servicePlan->id,
        'team_id' => $team->id,
        'description' => 'Standard plan option',
        'estimated_transit_time_unit' => TransitTimeUnit::Days,
        'maximum_estimated_transit_time' => 6,
        'minimum_estimated_transit_time' => 4,
        'name' => 'Standard plan',
        'weight_unit' => WeightUnit::Pounds,
    ]);
});

it('creates a record with only required fields', function (): void {
    $team = Team::factory()->createOne();

    $servicePlan = resolve(CreateServicePlan::class)->handle(
        $team,
        CreateServicePlanInput::from([
            'estimated_transit_time_unit' => TransitTimeUnit::Days(),
            'minimum_estimated_transit_time' => 4,
            'name' => 'Standard plan',
            'weight_unit' => WeightUnit::Pounds(),
        ]),
    );

    assertDatabaseHas(ServicePlan::class, [
        'id' => $servicePlan->id,
        'team_id' => $team->id,
        'description' => null,
        'maximum_estimated_transit_time' => null,
        'name' => 'Standard plan',
    ]);
});
```
