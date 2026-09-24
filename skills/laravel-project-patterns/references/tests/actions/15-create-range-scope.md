# Action Tests: Create Range Scope

Integration tests for range creation with retained data: ignore soft-deleted ranges, allow the same bounds under a different parent and recreate a range after soft deletion.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Actions\ServicePlans\CreatePlanRate;
use App\Actions\ServicePlans\Inputs\CreatePlanRateInput;
use App\Enums\CountryCode;
use App\Models\PlanRate;
use App\Models\PlanRule;

it('ignores soft deleted rates when creating ranges', function (): void {
    $planRule = PlanRule::factory()->createOne();
    PlanRate::factory()
        ->recycle($planRule)
        ->forRange(0, 2)
        ->trashed()
        ->createOne();

    resolve(CreatePlanRate::class)->handle(
        $planRule,
        CreatePlanRateInput::from([
            'maximum_weight' => '3',
            'minimum_weight' => '1',
            'name' => 'Standard Rate',
            'rate' => '12.5',
        ]),
    );

    assertDatabaseHas(PlanRate::class, [
        'team_id' => $planRule->team_id,
        'plan_rule_id' => $planRule->id,
        'maximum_weight' => '3.0000',
        'minimum_weight' => '1.0000',
    ]);
});

it('creates the same range in different rules', function (): void {
    $otherPlanRule = PlanRule::factory()
        ->createOne([
            'country_code' => CountryCode::China,
        ]);
    $planRule = PlanRule::factory()
        ->recycle($otherPlanRule->servicePlan)
        ->createOne([
            'country_code' => CountryCode::UnitedStates,
        ]);

    PlanRate::factory()
        ->recycle($planRule)
        ->forRange(0, 2)
        ->createOne();

    resolve(CreatePlanRate::class)->handle(
        $otherPlanRule,
        CreatePlanRateInput::from([
            'maximum_weight' => '2',
            'minimum_weight' => '0',
            'name' => 'Standard Rate',
            'rate' => '12.5',
        ]),
    );

    assertDatabaseHas(PlanRate::class, [
        'plan_rule_id' => $otherPlanRule->id,
        'maximum_weight' => '2.0000',
        'minimum_weight' => '0.0000',
    ]);
});

it('recreates a range after soft delete', function (): void {
    $rate = PlanRate::factory()
        ->forRange(5, null)
        ->trashed()
        ->createOne();
    $planRule = $rate->planRule;

    resolve(CreatePlanRate::class)->handle(
        $planRule,
        CreatePlanRateInput::from([
            'maximum_weight' => null,
            'minimum_weight' => '5',
            'name' => 'Standard Rate',
            'rate' => '12.5',
        ]),
    );

    assertDatabaseHas(PlanRate::class, [
        'team_id' => $planRule->team_id,
        'plan_rule_id' => $planRule->id,
        'maximum_weight' => null,
        'minimum_weight' => '5.0000',
    ]);
});
```
