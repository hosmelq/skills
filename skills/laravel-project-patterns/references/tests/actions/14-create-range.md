# Action Tests: Create Closed and Open Ranges

Integration tests for persisting bounded and open-ended numeric ranges, monetary rate formatting and adjacent boundaries within one parent. An omitted maximum creates an open end.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Actions\ServicePlans\CreatePlanRate;
use App\Actions\ServicePlans\Inputs\CreatePlanRateInput;
use App\Models\PlanRate;
use App\Models\PlanRule;

it('creates a record', function (): void {
    $planRule = PlanRule::factory()->createOne();

    $rate = resolve(CreatePlanRate::class)->handle(
        $planRule,
        CreatePlanRateInput::from([
            'maximum_weight' => '5',
            'minimum_weight' => '0.5',
            'name' => 'Standard Rate',
            'rate' => '12.5',
        ]),
    );

    expect($rate)->toBeInstanceOf(PlanRate::class);

    assertDatabaseHas(PlanRate::class, [
        'id' => $rate->id,
        'team_id' => $planRule->team_id,
        'plan_rule_id' => $planRule->id,
        'maximum_weight' => '5.0000',
        'minimum_weight' => '0.5000',
        'name' => 'Standard Rate',
        'rate' => '12.50',
    ]);
});

it('creates an open-ended rate when maximum weight is omitted', function (): void {
    $planRule = PlanRule::factory()->createOne();

    $rate = resolve(CreatePlanRate::class)->handle(
        $planRule,
        CreatePlanRateInput::from([
            'minimum_weight' => '0.5',
            'name' => 'Open Ended Rate',
            'rate' => '12.5',
        ]),
    );

    assertDatabaseHas(PlanRate::class, [
        'id' => $rate->id,
        'team_id' => $planRule->team_id,
        'plan_rule_id' => $planRule->id,
        'maximum_weight' => null,
        'minimum_weight' => '0.5000',
        'name' => 'Open Ended Rate',
        'rate' => '12.50',
    ]);
});

it('creates adjacent ranges within the same rule', function (): void {
    $planRule = PlanRule::factory()->createOne();

    PlanRate::factory()
        ->recycle($planRule)
        ->forRange(0, 1)
        ->createOne();

    $adjacentRate = resolve(CreatePlanRate::class)->handle(
        $planRule,
        CreatePlanRateInput::from([
            'maximum_weight' => '5',
            'minimum_weight' => '1',
            'name' => 'Standard Rate',
            'rate' => '12.5',
        ]),
    );

    $openEndedRate = resolve(CreatePlanRate::class)->handle(
        $planRule,
        CreatePlanRateInput::from([
            'maximum_weight' => null,
            'minimum_weight' => '5',
            'name' => 'Open Ended Rate',
            'rate' => '12.5',
        ]),
    );

    assertDatabaseHas(PlanRate::class, [
        'id' => $adjacentRate->id,
        'team_id' => $planRule->team_id,
        'plan_rule_id' => $planRule->id,
        'maximum_weight' => '5.0000',
        'minimum_weight' => '1.0000',
    ]);

    assertDatabaseHas(PlanRate::class, [
        'id' => $openEndedRate->id,
        'team_id' => $planRule->team_id,
        'plan_rule_id' => $planRule->id,
        'maximum_weight' => null,
        'minimum_weight' => '5.0000',
    ]);
});
```
