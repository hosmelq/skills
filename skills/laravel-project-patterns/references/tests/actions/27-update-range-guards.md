# Action Tests: Update Range Guards

Integration tests for updating a numeric range: reject an inactive ancestor, overlap and a second open-ended range. Preserve unchanged stored values after each rejected update.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Actions\ServicePlans\Inputs\UpdatePlanRateInput;
use App\Actions\ServicePlans\UpdatePlanRate;
use App\Exceptions\CannotUpdatePlanRate;
use App\Exceptions\CannotUseDeactivatedServicePlan;
use App\Models\PlanRate;
use App\Models\PlanRule;
use App\Models\ServicePlan;

it('rejects a deactivated ancestor', function (): void {
    $planRule = PlanRule::factory()
        ->for(ServicePlan::factory()->deactivated())
        ->createOne();
    $rate = PlanRate::factory()
        ->recycle($planRule)
        ->createOne(['name' => 'Original']);

    expect(fn () => resolve(UpdatePlanRate::class)->handle(
        $rate,
        UpdatePlanRateInput::from(['name' => 'Updated']),
    ))->toThrow(CannotUseDeactivatedServicePlan::class, 'Cannot use a deactivated service plan.');

    assertDatabaseHas(PlanRate::class, [
        'id' => $rate->id,
        'name' => 'Original',
    ]);
});

it('rejects overlapping ranges', function (): void {
    $rate = PlanRate::factory()
        ->forRange(3, 5)
        ->createOne(['name' => 'Original']);

    PlanRate::factory()
        ->recycle($rate->planRule)
        ->forRange(0, 2)
        ->createOne();

    expect(fn () => resolve(UpdatePlanRate::class)->handle(
        $rate,
        UpdatePlanRateInput::from([
            'maximum_weight' => '4',
            'minimum_weight' => '1',
        ]),
    ))->toThrow(
        CannotUpdatePlanRate::class,
        'The weight range overlaps an existing rate.',
    );

    assertDatabaseHas(PlanRate::class, [
        'id' => $rate->id,
        'maximum_weight' => '5.0000',
        'minimum_weight' => '3.0000',
        'name' => 'Original',
    ]);
});

it('rejects a second open-ended range', function (): void {
    $rate = PlanRate::factory()
        ->forRange(0, 5)
        ->createOne();

    PlanRate::factory()
        ->recycle($rate->planRule)
        ->forRange(10, null)
        ->createOne();

    expect(fn () => resolve(UpdatePlanRate::class)->handle(
        $rate,
        UpdatePlanRateInput::from(['maximum_weight' => null]),
    ))->toThrow(
        CannotUpdatePlanRate::class,
        'Only one open-ended rate is allowed per plan rule.',
    );

    assertDatabaseHas(PlanRate::class, [
        'id' => $rate->id,
        'maximum_weight' => '5.0000',
    ]);
});
```
