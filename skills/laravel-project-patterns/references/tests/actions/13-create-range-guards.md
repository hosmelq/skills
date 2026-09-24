# Action Tests: Create Range Guards

Integration tests for range creation: reject an inactive ancestor, overlapping bounds and a second open-ended range within one parent. Keep exception messages and unchanged database state.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseMissing;

use App\Actions\ServicePlans\CreatePlanRate;
use App\Actions\ServicePlans\Inputs\CreatePlanRateInput;
use App\Exceptions\CannotCreatePlanRate;
use App\Exceptions\CannotUseDeactivatedServicePlan;
use App\Models\PlanRate;
use App\Models\PlanRule;
use App\Models\ServicePlan;

it('rejects a deactivated ancestor', function (): void {
    $planRule = PlanRule::factory()
        ->for(ServicePlan::factory()->deactivated())
        ->createOne();

    expect(fn () => resolve(CreatePlanRate::class)->handle(
        $planRule,
        CreatePlanRateInput::from([
            'maximum_weight' => '5',
            'minimum_weight' => '0.5',
            'name' => 'Standard Rate',
            'rate' => '12.5',
        ]),
    ))->toThrow(
        CannotUseDeactivatedServicePlan::class,
        'Cannot use a deactivated service plan.',
    );

    assertDatabaseMissing(PlanRate::class, [
        'plan_rule_id' => $planRule->id,
        'name' => 'Standard Rate',
    ]);
});

it('rejects overlapping ranges', function (): void {
    $planRule = PlanRule::factory()->createOne();

    PlanRate::factory()
        ->recycle($planRule)
        ->forRange(0, 2)
        ->createOne();

    expect(fn () => resolve(CreatePlanRate::class)->handle(
        $planRule,
        CreatePlanRateInput::from([
            'maximum_weight' => '3',
            'minimum_weight' => '1',
            'name' => 'Standard Rate',
            'rate' => '12.5',
        ]),
    ))->toThrow(
        CannotCreatePlanRate::class,
        'The weight range overlaps an existing rate.',
    );

    assertDatabaseMissing(PlanRate::class, [
        'plan_rule_id' => $planRule->id,
        'name' => 'Standard Rate',
    ]);
});

it('rejects a second open-ended range', function (): void {
    $planRule = PlanRule::factory()->createOne();

    PlanRate::factory()
        ->recycle($planRule)
        ->forRange(5, null)
        ->createOne();

    expect(fn () => resolve(CreatePlanRate::class)->handle(
        $planRule,
        CreatePlanRateInput::from([
            'maximum_weight' => null,
            'minimum_weight' => '10',
            'name' => 'Standard Rate',
            'rate' => '12.5',
        ]),
    ))->toThrow(
        CannotCreatePlanRate::class,
        'Only one open-ended rate is allowed per plan rule.',
    );

    assertDatabaseMissing(PlanRate::class, [
        'plan_rule_id' => $planRule->id,
        'name' => 'Standard Rate',
    ]);
});
```
