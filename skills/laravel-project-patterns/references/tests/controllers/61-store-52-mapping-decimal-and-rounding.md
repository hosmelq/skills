# Store Tests: Mapping Decimal And Rounding

Pest POST store: Country/currency enums and numeric zero to string, plus clearing explicit increment when rounding is disabled.

## Complete block

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
use App\Models\PlanRule;
use App\Models\ServicePlan;

describe('store', function (): void {
    it('stores the record', function (): void {
        $servicePlan = ServicePlan::factory()->createOne();
        $planRule = PlanRule::factory()
            ->for($servicePlan)
            ->forCountry(CountryCode::Nicaragua)
            ->createOne();

        signIn(team: $servicePlan->team);

        mock(CreatePlanRule::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (ServicePlan $servicePlanArgument, CreatePlanRuleInput $input): bool => $servicePlanArgument->is($servicePlan)
                && $input->countryCode === CountryCode::Japan
                && $input->currencyCode === CurrencyCode::CNY
                && $input->minimumBillableWeight === '0')
            ->andReturn($planRule);

        $response = post(route('teams.service-plans.plan-rules.store', [
            'team' => $servicePlan->team,
            'service_plan' => $servicePlan,
        ]), [
            'billable_weight_rounding_mode' => BillableWeightRoundingMode::None->value,
            'country_code' => CountryCode::Japan->value,
            'currency_code' => CurrencyCode::CNY->value,
            'minimum_billable_weight' => 0,
            'name' => 'China air cargo',
        ]);

        $response->assertRedirectToRoute('teams.service-plans.plan-rules.show', [
            'team' => $servicePlan->team,
            'service_plan' => $servicePlan,
            'plan_rule' => $planRule,
        ])
            ->assertToast('Plan rule created');
    });

    it('clears the rounding increment when rounding is disabled', function (): void {
        $servicePlan = ServicePlan::factory()->createOne();
        $planRule = PlanRule::factory()
            ->for($servicePlan)
            ->forCountry(CountryCode::Nicaragua)
            ->createOne();

        signIn(team: $servicePlan->team);

        mock(CreatePlanRule::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (ServicePlan $servicePlanArgument, CreatePlanRuleInput $input): bool => $servicePlanArgument->is($servicePlan)
                && $input->billableWeightRoundingIncrement === null
                && $input->billableWeightRoundingMode === BillableWeightRoundingMode::None)
            ->andReturn($planRule);

        $response = post(route('teams.service-plans.plan-rules.store', [
            'team' => $servicePlan->team,
            'service_plan' => $servicePlan,
        ]), [
            'billable_weight_rounding_increment' => 1,
            'billable_weight_rounding_mode' => BillableWeightRoundingMode::None->value,
            'country_code' => CountryCode::Canada->value,
            'currency_code' => CurrencyCode::USD->value,
            'minimum_billable_weight' => 1,
            'name' => 'US air cargo',
        ]);

        $response->assertRedirectToRoute('teams.service-plans.plan-rules.show', [
            'team' => $servicePlan->team,
            'service_plan' => $servicePlan,
            'plan_rule' => $planRule,
        ])
            ->assertToast('Plan rule created');
    });
});
```
