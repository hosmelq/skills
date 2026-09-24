# Action Tests: Update Range Bounds

Integration tests for range updates with a numeric rate: full input, adjacent boundaries, omitted-field preservation, explicit nulls and a changed minimum when the stored maximum is open-ended.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Actions\ServicePlans\Inputs\UpdatePlanRateInput;
use App\Actions\ServicePlans\UpdatePlanRate;
use App\Models\PlanRate;

it('updates a record under an active ancestor', function (): void {
    $rate = PlanRate::factory()->createOne([
        'maximum_weight' => '10',
        'minimum_weight' => '1',
        'name' => 'Original',
        'rate' => '2.50',
    ]);

    resolve(UpdatePlanRate::class)->handle(
        $rate,
        UpdatePlanRateInput::from([
            'maximum_weight' => '12',
            'minimum_weight' => '2',
            'name' => 'Updated',
            'rate' => '25.99',
        ]),
    );

    assertDatabaseHas(PlanRate::class, [
        'id' => $rate->id,
        'maximum_weight' => '12.0000',
        'minimum_weight' => '2.0000',
        'name' => 'Updated',
        'rate' => '25.99',
    ]);
});

it('updates a rate between adjacent ranges', function (): void {
    $rate = PlanRate::factory()
        ->forRange(3, 5)
        ->createOne();

    PlanRate::factory()
        ->recycle($rate->planRule)
        ->forRange(0, 2)
        ->createOne();

    PlanRate::factory()
        ->recycle($rate->planRule)
        ->forRange(6, 8)
        ->createOne();

    resolve(UpdatePlanRate::class)->handle(
        $rate,
        UpdatePlanRateInput::from([
            'maximum_weight' => '6',
            'minimum_weight' => '2',
        ]),
    );

    assertDatabaseHas(PlanRate::class, [
        'id' => $rate->id,
        'maximum_weight' => '6.0000',
        'minimum_weight' => '2.0000',
    ]);
});

it('updates only provided fields', function (): void {
    $rate = PlanRate::factory()->createOne();

    resolve(UpdatePlanRate::class)->handle(
        $rate,
        UpdatePlanRateInput::from(['name' => 'Updated without range conflict']),
    );

    assertDatabaseHas(PlanRate::class, [
        'id' => $rate->id,
        'maximum_weight' => $rate->maximum_weight,
        'minimum_weight' => $rate->minimum_weight,
        'name' => 'Updated without range conflict',
        'rate' => $rate->rate,
    ]);
});

it('clears nullable fields', function (): void {
    $rate = PlanRate::factory()->createOne([
        'maximum_weight' => '10',
    ]);

    resolve(UpdatePlanRate::class)->handle(
        $rate,
        UpdatePlanRateInput::from(['maximum_weight' => null]),
    );

    assertDatabaseHas(PlanRate::class, [
        'id' => $rate->id,
        'maximum_weight' => null,
    ]);
});

it('allows minimum weight updates when the stored maximum weight is open ended', function (): void {
    $rate = PlanRate::factory()
        ->forRange(5, null)
        ->createOne();

    resolve(UpdatePlanRate::class)->handle(
        $rate,
        UpdatePlanRateInput::from(['minimum_weight' => '11']),
    );

    assertDatabaseHas(PlanRate::class, [
        'id' => $rate->id,
        'maximum_weight' => null,
        'minimum_weight' => '11.0000',
    ]);
});
```
