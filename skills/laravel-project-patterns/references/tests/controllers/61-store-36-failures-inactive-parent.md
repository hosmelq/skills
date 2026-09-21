# Store Tests: Failures Inactive Parent

Pest POST store: Initially active parent/ancestor reaches action, which throws inactive-state exception; different bound parent types and DTO predicates remain.

The action is mocked to throw. These assertions verify controller translation, not the underlying business guard.

## Rejects storing when the parent becomes inactive — variant 1

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\post;

use App\Actions\ServicePlans\CreatePlanRule;
use App\Actions\ServicePlans\Inputs\CreatePlanRuleInput;
use App\Enums\BillableWeightRoundingMode;
use App\Enums\CountryCode;
use App\Enums\CurrencyCode;
use App\Exceptions\CannotUseDeactivatedServicePlan;
use App\Models\ServicePlan;

describe('store', function (): void {
    it('rejects storing when the parent becomes inactive', function (): void {
        $servicePlan = ServicePlan::factory()->createOne();

        signIn(team: $servicePlan->team);

        mock(CreatePlanRule::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (ServicePlan $servicePlanArgument, CreatePlanRuleInput $input): bool => $servicePlanArgument->is($servicePlan)
                && $input->countryCode === CountryCode::Nicaragua
                && $input->currencyCode === CurrencyCode::USD)
            ->andThrow(CannotUseDeactivatedServicePlan::becauseItIsDeactivated());

        $response = post(route('teams.service-plans.plan-rules.store', [
            'team' => $servicePlan->team,
            'service_plan' => $servicePlan,
        ]), [
            'billable_weight_rounding_mode' => BillableWeightRoundingMode::None->value,
            'country_code' => CountryCode::Nicaragua->value,
            'currency_code' => CurrencyCode::USD->value,
            'minimum_billable_weight' => 1,
            'name' => 'Nicaragua',
        ]);

        $response->assertRedirectBackWithErrors([
            'service_plan' => 'This service plan is deactivated.',
        ]);
    });
});
```

## Rejects storing when the ancestor becomes inactive — variant 2

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
    it('rejects storing when the ancestor becomes inactive', function (): void {
        $planRule = PlanRule::factory()->createOne();

        signIn(team: $planRule->servicePlan->team);

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
