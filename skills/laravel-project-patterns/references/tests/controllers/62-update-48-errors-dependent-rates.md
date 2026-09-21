# Update Tests: Errors Dependent Rates

Pest PATCH update: Mocked dependent-rate errors prevent a plan weight-unit or rule currency update through validation responses; typed fields and exception factories remain distinct.

The action is mocked to throw. These assertions verify controller translation, not the underlying business guard.

## Maps a dependent rate rejection to validation — variant 1

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\patch;

use App\Actions\ServicePlans\Inputs\UpdateServicePlanInput;
use App\Actions\ServicePlans\UpdateServicePlan;
use App\Enums\WeightUnit;
use App\Exceptions\CannotUpdateServicePlan;
use App\Models\ServicePlan;

describe('update', function (): void {
    it('maps a dependent rate rejection to validation', function (): void {
        $servicePlan = ServicePlan::factory()->createOne();

        signIn(team: $servicePlan->team);

        mock(UpdateServicePlan::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (ServicePlan $servicePlanArgument, UpdateServicePlanInput $input): bool => $servicePlanArgument->is($servicePlan)
                && $input->weightUnit === WeightUnit::Kilograms)
            ->andThrow(CannotUpdateServicePlan::becauseItHasRates());

        $response = patch(route('teams.service-plans.update', [
            'team' => $servicePlan->team,
            'service_plan' => $servicePlan,
        ]), [
            'weight_unit' => WeightUnit::Kilograms(),
        ]);

        $response->assertRedirectBackWithErrors([
            'weight_unit' => 'The weight unit field is prohibited.',
        ]);
    });
});
```

## Maps a dependent rate rejection to validation — variant 2

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\patch;

use App\Actions\ServicePlans\Inputs\UpdatePlanRuleInput;
use App\Actions\ServicePlans\UpdatePlanRule;
use App\Enums\CurrencyCode;
use App\Exceptions\CannotUpdatePlanRule;
use App\Models\PlanRule;

describe('update', function (): void {
    it('maps a dependent rate rejection to validation', function (): void {
        $planRule = PlanRule::factory()->createOne();

        signIn(team: $planRule->servicePlan->team);

        mock(UpdatePlanRule::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (PlanRule $planRuleArgument, UpdatePlanRuleInput $input): bool => $planRuleArgument->is($planRule)
                && $input->currencyCode === CurrencyCode::CNY)
            ->andThrow(CannotUpdatePlanRule::becauseItHasRates());

        $response = patch(route('teams.service-plans.plan-rules.update', [
            'team' => $planRule->servicePlan->team,
            'service_plan' => $planRule->servicePlan,
            'plan_rule' => $planRule,
        ]), [
            'currency_code' => CurrencyCode::CNY->value,
        ]);

        $response->assertRedirectBackWithErrors([
            'currency_code' => 'The currency code field is prohibited.',
        ]);
    });
});
```
