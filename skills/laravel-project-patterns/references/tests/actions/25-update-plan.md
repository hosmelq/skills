# Action Tests: Update Fields with Dependent Rates

Integration tests for updating a configured parent with dependent rates: reject a provided measurement unit, observe row-lock SQL, allow omitted units, preserve omitted fields and clear nullable fields.

Use the inspected test helper or the [query observer](01-row-lock-observation.md); it must be registered before invoking the action.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Actions\ServicePlans\Inputs\UpdateServicePlanInput;
use App\Actions\ServicePlans\UpdateServicePlan;
use App\Enums\TransitTimeUnit;
use App\Enums\WeightUnit;
use App\Exceptions\CannotUpdateServicePlan;
use App\Models\PlanRate;
use App\Models\ServicePlan;

it('rejects weight unit updates when rates exist', function (): void {
    $rate = PlanRate::factory()->createOne();

    expect(fn () => resolve(UpdateServicePlan::class)->handle(
        $rate->planRule->servicePlan,
        UpdateServicePlanInput::from(['weight_unit' => WeightUnit::Kilograms()]),
    ))->toThrow(
        CannotUpdateServicePlan::class,
        'Cannot update a service plan weight unit with rates.',
    );

    assertDatabaseHas(ServicePlan::class, [
        'id' => $rate->planRule->servicePlan->id,
        'weight_unit' => $rate->planRule->servicePlan->weight_unit,
    ]);
});

it('observes a row-lock query', function (): void {
    $servicePlan = ServicePlan::factory()->createOne();

    assertRowLockQueryObserved($servicePlan);

    resolve(UpdateServicePlan::class)->handle(
        $servicePlan,
        UpdateServicePlanInput::from(['name' => 'Extended plan']),
    );
});

it('updates a record', function (): void {
    $servicePlan = ServicePlan::factory()->createOne([
        'description' => 'Standard plan option',
        'maximum_estimated_transit_time' => 6,
        'minimum_estimated_transit_time' => 4,
        'name' => 'Standard plan',
    ]);

    $updatedServicePlan = resolve(UpdateServicePlan::class)->handle(
        $servicePlan,
        UpdateServicePlanInput::from([
            'description' => 'Extended plan option',
            'estimated_transit_time_unit' => TransitTimeUnit::Weeks(),
            'maximum_estimated_transit_time' => 7,
            'minimum_estimated_transit_time' => 5,
            'name' => 'Extended plan',
            'weight_unit' => WeightUnit::Kilograms(),
        ]),
    );

    expect($updatedServicePlan->is($servicePlan))->toBeTrue();

    assertDatabaseHas(ServicePlan::class, [
        'id' => $servicePlan->id,
        'description' => 'Extended plan option',
        'estimated_transit_time_unit' => TransitTimeUnit::Weeks,
        'maximum_estimated_transit_time' => 7,
        'minimum_estimated_transit_time' => 5,
        'name' => 'Extended plan',
        'weight_unit' => WeightUnit::Kilograms,
    ]);
});

it('updates provided fields when rates exist and weight unit is omitted', function (): void {
    $rate = PlanRate::factory()->createOne();
    $servicePlan = $rate->planRule->servicePlan;

    resolve(UpdateServicePlan::class)->handle(
        $servicePlan,
        UpdateServicePlanInput::from(['name' => 'Extended plan']),
    );

    assertDatabaseHas(ServicePlan::class, [
        'id' => $servicePlan->id,
        'name' => 'Extended plan',
        'weight_unit' => $servicePlan->weight_unit,
    ]);
});

it('updates only provided fields', function (): void {
    $servicePlan = ServicePlan::factory()->createOne([
        'description' => 'Standard plan option',
        'name' => 'Standard plan',
    ]);

    resolve(UpdateServicePlan::class)->handle(
        $servicePlan,
        UpdateServicePlanInput::from(['name' => 'Extended plan']),
    );

    assertDatabaseHas(ServicePlan::class, [
        'id' => $servicePlan->id,
        'description' => 'Standard plan option',
        'name' => 'Extended plan',
    ]);
});

it('clears nullable fields', function (): void {
    $servicePlan = ServicePlan::factory()->createOne([
        'description' => 'Standard plan option',
        'maximum_estimated_transit_time' => 6,
    ]);

    resolve(UpdateServicePlan::class)->handle(
        $servicePlan,
        UpdateServicePlanInput::from([
            'description' => null,
            'maximum_estimated_transit_time' => null,
        ]),
    );

    assertDatabaseHas(ServicePlan::class, [
        'id' => $servicePlan->id,
        'description' => null,
        'maximum_estimated_transit_time' => null,
    ]);
});
```
