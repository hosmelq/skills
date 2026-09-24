# Action Tests: Update Currency with Dependent Rates

Integration tests for a child under an active parent: reject inactivity and a currency change when rates exist; allow unchanged or omitted currency, change currency without rates and clear nullable fields.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Actions\ServicePlans\Inputs\UpdatePlanRuleInput;
use App\Actions\ServicePlans\UpdatePlanRule;
use App\Enums\CountryCode;
use App\Enums\CurrencyCode;
use App\Enums\RoundingMode;
use App\Exceptions\CannotUpdatePlanRule;
use App\Exceptions\CannotUseDeactivatedServicePlan;
use App\Models\PlanRate;
use App\Models\PlanRule;
use App\Models\ServicePlan;

it('rejects deactivated parent records', function (): void {
    $planRule = PlanRule::factory()
        ->for(ServicePlan::factory()->deactivated())
        ->createOne(['name' => 'Original']);

    expect(fn () => resolve(UpdatePlanRule::class)->handle(
        $planRule,
        UpdatePlanRuleInput::from(['name' => 'Updated']),
    ))->toThrow(CannotUseDeactivatedServicePlan::class, 'Cannot use a deactivated service plan.');

    assertDatabaseHas(PlanRule::class, [
        'id' => $planRule->id,
        'name' => 'Original',
    ]);
});

it('rejects currency updates when rates exist', function (): void {
    $planRule = PlanRule::factory()->createOne([
        'currency_code' => CurrencyCode::USD,
    ]);

    PlanRate::factory()->recycle($planRule)->createOne();

    expect(fn () => resolve(UpdatePlanRule::class)->handle(
        $planRule,
        UpdatePlanRuleInput::from(['currency_code' => CurrencyCode::CNY()]),
    ))->toThrow(
        CannotUpdatePlanRule::class,
        'Cannot update a plan rule currency with rates.',
    );

    assertDatabaseHas(PlanRule::class, [
        'id' => $planRule->id,
        'currency_code' => $planRule->currency_code,
    ]);
});

it('updates provided fields when rates exist and currency is unchanged', function (): void {
    $planRule = PlanRule::factory()->createOne();

    PlanRate::factory()->recycle($planRule)->createOne();

    resolve(UpdatePlanRule::class)->handle(
        $planRule,
        UpdatePlanRuleInput::from([
            'currency_code' => $planRule->currency_code->value,
            'name' => 'Updated',
        ]),
    );

    assertDatabaseHas(PlanRule::class, [
        'id' => $planRule->id,
        'currency_code' => $planRule->currency_code,
        'name' => 'Updated',
    ]);
});

it('updates a rule currency when rates do not exist', function (): void {
    $planRule = PlanRule::factory()->roundUp()->createOne([
        'country_code' => CountryCode::Canada,
        'currency_code' => CurrencyCode::USD,
        'minimum_chargeable_weight' => '5',
        'name' => 'Original',
    ]);

    resolve(UpdatePlanRule::class)->handle(
        $planRule,
        UpdatePlanRuleInput::from([
            'currency_code' => CurrencyCode::CNY(),
            'name' => 'Updated',
        ]),
    );

    assertDatabaseHas(PlanRule::class, [
        'id' => $planRule->id,
        'country_code' => CountryCode::Canada,
        'currency_code' => CurrencyCode::CNY,
        'minimum_chargeable_weight' => '5.0000',
        'name' => 'Updated',
        'rounding_increment' => '1.0000',
        'rounding_mode' => RoundingMode::Up,
    ]);
});

it('updates provided fields when rates exist and currency is omitted', function (): void {
    $planRule = PlanRule::factory()->createOne();

    PlanRate::factory()->recycle($planRule)->createOne();

    resolve(UpdatePlanRule::class)->handle(
        $planRule,
        UpdatePlanRuleInput::from(['name' => 'Updated']),
    );

    assertDatabaseHas(PlanRule::class, [
        'id' => $planRule->id,
        'currency_code' => $planRule->currency_code,
        'name' => 'Updated',
    ]);
});

it('clears nullable fields', function (): void {
    $planRule = PlanRule::factory()->roundUp()->createOne();

    resolve(UpdatePlanRule::class)->handle(
        $planRule,
        UpdatePlanRuleInput::from([
            'rounding_increment' => null,
            'rounding_mode' => RoundingMode::None(),
        ]),
    );

    assertDatabaseHas(PlanRule::class, [
        'id' => $planRule->id,
        'rounding_increment' => null,
        'rounding_mode' => RoundingMode::None,
    ]);
});
```
