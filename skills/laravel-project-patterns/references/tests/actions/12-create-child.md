# Action Tests: Create Under an Active Parent

Integration tests for child creation under an active parent: reject inactivity, observe row-lock SQL, persist full input and required-only defaults including currency, country and rounding fields.

Use the inspected test helper or the [query observer](01-row-lock-observation.md); it must be registered before invoking the action.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

use App\Actions\ServicePlans\CreatePlanRule;
use App\Actions\ServicePlans\Inputs\CreatePlanRuleInput;
use App\Enums\CountryCode;
use App\Enums\CurrencyCode;
use App\Enums\RoundingMode;
use App\Exceptions\CannotUseDeactivatedServicePlan;
use App\Models\PlanRule;
use App\Models\ServicePlan;

it('rejects deactivated parent records', function (): void {
    $servicePlan = ServicePlan::factory()->deactivated()->createOne();

    expect(fn () => resolve(CreatePlanRule::class)->handle(
        $servicePlan,
        CreatePlanRuleInput::from([
            'country_code' => CountryCode::UnitedStates(),
            'currency_code' => CurrencyCode::USD(),
            'minimum_chargeable_weight' => '1',
            'name' => 'United States',
            'rounding_mode' => RoundingMode::None(),
        ]),
    ))->toThrow(CannotUseDeactivatedServicePlan::class, 'Cannot use a deactivated service plan.');

    assertDatabaseMissing(PlanRule::class, [
        'service_plan_id' => $servicePlan->id,
        'name' => 'United States',
    ]);
});

it('observes a row-lock query', function (): void {
    $servicePlan = ServicePlan::factory()->createOne();

    assertRowLockQueryObserved($servicePlan);

    resolve(CreatePlanRule::class)->handle(
        $servicePlan,
        CreatePlanRuleInput::from([
            'country_code' => CountryCode::UnitedStates(),
            'currency_code' => CurrencyCode::USD(),
            'minimum_chargeable_weight' => '1',
            'name' => 'United States',
            'rounding_mode' => RoundingMode::None(),
        ]),
    );
});

it('creates a record under an active parent', function (): void {
    $servicePlan = ServicePlan::factory()->createOne();

    $planRule = resolve(CreatePlanRule::class)->handle(
        $servicePlan,
        CreatePlanRuleInput::from([
            'country_code' => CountryCode::UnitedStates(),
            'currency_code' => CurrencyCode::USD(),
            'minimum_chargeable_weight' => '11',
            'name' => 'United States',
            'rounding_increment' => '0.5',
            'rounding_mode' => RoundingMode::Up(),
        ]),
    );

    assertDatabaseHas(PlanRule::class, [
        'id' => $planRule->id,
        'team_id' => $servicePlan->team_id,
        'service_plan_id' => $servicePlan->id,
        'country_code' => CountryCode::UnitedStates,
        'currency_code' => CurrencyCode::USD,
        'minimum_chargeable_weight' => '11.0000',
        'name' => 'United States',
        'rounding_increment' => '0.5000',
        'rounding_mode' => RoundingMode::Up,
    ]);
});

it('creates a record with only required fields', function (): void {
    $servicePlan = ServicePlan::factory()->createOne();

    $planRule = resolve(CreatePlanRule::class)->handle(
        $servicePlan,
        CreatePlanRuleInput::from([
            'country_code' => CountryCode::UnitedStates(),
            'currency_code' => CurrencyCode::USD(),
            'minimum_chargeable_weight' => '1',
            'name' => 'United States',
            'rounding_mode' => RoundingMode::None(),
        ]),
    );

    assertDatabaseHas(PlanRule::class, [
        'id' => $planRule->id,
        'team_id' => $servicePlan->team_id,
        'service_plan_id' => $servicePlan->id,
        'country_code' => CountryCode::UnitedStates,
        'currency_code' => CurrencyCode::USD,
        'minimum_chargeable_weight' => '1.0000',
        'name' => 'United States',
        'rounding_increment' => null,
        'rounding_mode' => RoundingMode::None,
    ]);
});
```
