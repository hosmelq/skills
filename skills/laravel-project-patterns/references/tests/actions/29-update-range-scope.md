# Action Tests: Update Range Scope

Integration tests for updating range boundaries: ignore soft-deleted ranges, allow matching bounds under another parent and reuse an open-ended range after soft deletion.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Actions\ServicePlans\Inputs\UpdatePlanRateInput;
use App\Actions\ServicePlans\UpdatePlanRate;
use App\Enums\CountryCode;
use App\Models\PlanRate;
use App\Models\PlanRule;

it('ignores soft deleted rates when updating ranges', function (): void {
    $rate = PlanRate::factory()
        ->forRange(3, 5)
        ->createOne();

    PlanRate::factory()
        ->recycle($rate->planRule)
        ->forRange(0, 2)
        ->trashed()
        ->createOne();

    resolve(UpdatePlanRate::class)->handle(
        $rate,
        UpdatePlanRateInput::from([
            'maximum_weight' => '3',
            'minimum_weight' => '1',
        ]),
    );

    assertDatabaseHas(PlanRate::class, [
        'id' => $rate->id,
        'maximum_weight' => '3.0000',
        'minimum_weight' => '1.0000',
    ]);
});

it('updates a rate to match a range in another rule', function (): void {
    $otherPlanRule = PlanRule::factory()
        ->createOne([
            'country_code' => CountryCode::China,
        ]);
    $planRule = PlanRule::factory()
        ->recycle($otherPlanRule->servicePlan)
        ->createOne([
            'country_code' => CountryCode::UnitedStates,
        ]);
    $rate = PlanRate::factory()
        ->recycle($planRule)
        ->forRange(3, 5)
        ->createOne();

    PlanRate::factory()
        ->recycle($otherPlanRule)
        ->forRange(0, 2)
        ->createOne();

    resolve(UpdatePlanRate::class)->handle(
        $rate,
        UpdatePlanRateInput::from([
            'maximum_weight' => '2',
            'minimum_weight' => '0',
        ]),
    );

    assertDatabaseHas(PlanRate::class, [
        'id' => $rate->id,
        'maximum_weight' => '2.0000',
        'minimum_weight' => '0.0000',
    ]);
});

it('reuses an open-ended range after soft delete', function (): void {
    $rate = PlanRate::factory()
        ->forRange(0, 5)
        ->createOne();

    PlanRate::factory()
        ->recycle($rate->planRule)
        ->forRange(10, null)
        ->trashed()
        ->createOne();

    resolve(UpdatePlanRate::class)->handle(
        $rate,
        UpdatePlanRateInput::from(['maximum_weight' => null]),
    );

    assertDatabaseHas(PlanRate::class, [
        'id' => $rate->id,
        'maximum_weight' => null,
    ]);
});
```
