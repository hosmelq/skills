# Update Tests: Errors Inactive Plan

PATCH update: Mocked inactive-plan rejection for a rule and rate; preserve request payload and exception mapping instead of replacing them with real inactive 403 fixtures.

## Maps an inactive parent rejection to validation

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\patch;

use App\Actions\ServicePlans\Inputs\UpdatePlanRuleInput;
use App\Actions\ServicePlans\UpdatePlanRule;
use App\Exceptions\CannotUseDeactivatedServicePlan;
use App\Models\PlanRule;

describe('update', function (): void {
    it('maps an inactive parent rejection to validation', function (): void {
        $planRule = PlanRule::factory()->createOne();

        login(team: $planRule->servicePlan->team);

        mock(UpdatePlanRule::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (PlanRule $planRuleArgument, UpdatePlanRuleInput $input): bool => $planRuleArgument->is($planRule)
                && $input->name === 'Updated')
            ->andThrow(CannotUseDeactivatedServicePlan::becauseItIsDeactivated());

        $response = patch(route('teams.service-plans.plan-rules.update', [
            'team' => $planRule->servicePlan->team,
            'service_plan' => $planRule->servicePlan,
            'plan_rule' => $planRule,
        ]), [
            'name' => 'Updated',
        ]);

        $response->assertRedirectBackWithErrors([
            'service_plan' => 'This service plan is deactivated.',
        ]);
    });
});
```

## Maps an inactive ancestor rejection to validation

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\patch;

use App\Actions\ServicePlans\Inputs\UpdatePlanRateInput;
use App\Actions\ServicePlans\UpdatePlanRate;
use App\Exceptions\CannotUseDeactivatedServicePlan;
use App\Models\PlanRate;

describe('update', function (): void {
    it('maps an inactive ancestor rejection to validation', function (): void {
        $rate = PlanRate::factory()->createOne();

        login(team: $rate->planRule->servicePlan->team);

        mock(UpdatePlanRate::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (
                PlanRate $rateArgument,
                UpdatePlanRateInput $input,
            ): bool => $rateArgument->is($rate)
                && $input->name === 'Updated')
            ->andThrow(CannotUseDeactivatedServicePlan::becauseItIsDeactivated());

        $response = patch(route('teams.service-plans.plan-rules.rates.update', [
            'team' => $rate->planRule->servicePlan->team,
            'service_plan' => $rate->planRule->servicePlan,
            'plan_rule' => $rate->planRule,
            'rate' => $rate,
        ]), [
            'name' => 'Updated',
        ]);

        $response->assertRedirectBackWithErrors([
            'service_plan' => 'This service plan is deactivated.',
        ]);
    });
});
```
