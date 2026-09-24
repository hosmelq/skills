# Store Tests: Failures Inactive Parent

POST store: Initially active parent/ancestor reaches action, which throws inactive-state exception; different bound parent types and DTO predicates remain.

The action is mocked to throw. These assertions verify controller translation, not the underlying business guard.

## Inactive parent rejection

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\post;

use App\Actions\ServicePlans\CreatePlanRule;
use App\Actions\ServicePlans\Inputs\CreatePlanRuleInput;
use App\Enums\CountryCode;
use App\Enums\CurrencyCode;
use App\Enums\RoundingMode;
use App\Exceptions\CannotUseDeactivatedServicePlan;
use App\Models\ServicePlan;

describe('store', function (): void {
    it('maps an inactive parent rejection to validation', function (): void {
        $servicePlan = ServicePlan::factory()->createOne();

        login(team: $servicePlan->team);

        mock(CreatePlanRule::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (ServicePlan $servicePlanArgument, CreatePlanRuleInput $input): bool => $servicePlanArgument->is($servicePlan)
                && $input->countryCode === CountryCode::UnitedStates
                && $input->currencyCode === CurrencyCode::USD)
            ->andThrow(CannotUseDeactivatedServicePlan::becauseItIsDeactivated());

        $response = post(route('teams.service-plans.plan-rules.store', [
            'team' => $servicePlan->team,
            'service_plan' => $servicePlan,
        ]), [
            'country_code' => CountryCode::UnitedStates->value,
            'currency_code' => CurrencyCode::USD->value,
            'minimum_chargeable_weight' => 1,
            'name' => 'Default rule',
            'rounding_mode' => RoundingMode::None->value,
        ]);

        $response->assertRedirectBackWithErrors([
            'service_plan' => 'This service plan is deactivated.',
        ]);
    });
});
```

## Inactive ancestor rejection

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\post;

use App\Actions\ServicePlans\CreatePlanRate;
use App\Actions\ServicePlans\Inputs\CreatePlanRateInput;
use App\Exceptions\CannotUseDeactivatedServicePlan;
use App\Models\PlanRule;

describe('store', function (): void {
    it('maps an inactive ancestor rejection to validation', function (): void {
        $planRule = PlanRule::factory()->createOne();

        login(team: $planRule->servicePlan->team);

        mock(CreatePlanRate::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (PlanRule $planRuleArgument, CreatePlanRateInput $input): bool => $planRuleArgument->is($planRule)
                && $input->minimumWeight === '0'
                && $input->name === '0 to 5'
                && $input->rate === '2.50')
            ->andThrow(CannotUseDeactivatedServicePlan::becauseItIsDeactivated());

        $response = post(route('teams.service-plans.plan-rules.rates.store', [
            'team' => $planRule->servicePlan->team,
            'service_plan' => $planRule->servicePlan,
            'plan_rule' => $planRule,
        ]), [
            'maximum_weight' => '5',
            'minimum_weight' => '0',
            'name' => '0 to 5',
            'rate' => '2.50',
        ]);

        $response->assertRedirectBackWithErrors([
            'service_plan' => 'This service plan is deactivated.',
        ]);
    });
});
```
