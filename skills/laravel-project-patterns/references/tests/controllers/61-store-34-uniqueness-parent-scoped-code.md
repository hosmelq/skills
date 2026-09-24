# Store Tests: Uniqueness Parent Scoped Code

Pest POST store: Country-code uniqueness per parent, reuse under another parent in same tenant and after deletion.

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
    it('rejects a duplicate value in the same scope', function (): void {
        $planRule = PlanRule::factory()->createOne([
            'country_code' => CountryCode::Canada,
        ]);

        signIn(team: $planRule->servicePlan->team);

        $response = post(route('teams.service-plans.plan-rules.store', [
            'team' => $planRule->servicePlan->team,
            'service_plan' => $planRule->servicePlan,
        ]), [
            'billable_weight_rounding_mode' => BillableWeightRoundingMode::None->value,
            'country_code' => CountryCode::Canada->value,
            'currency_code' => CurrencyCode::USD->value,
            'minimum_billable_weight' => 1,
            'name' => 'Canada air cargo',
        ]);

        $response->assertRedirectBackWithErrors([
            'country_code' => 'The country code has already been taken.',
        ]);
    });

    it('allows a value used in a different scope', function (): void {
        $planRule = PlanRule::factory()->createOne([
            'country_code' => CountryCode::Canada,
        ]);

        $servicePlan = ServicePlan::factory()
            ->for($planRule->servicePlan->team)
            ->createOne();
        $createdPlanRule = PlanRule::factory()
            ->for($servicePlan)
            ->forCountry(CountryCode::Japan)
            ->createOne();

        signIn(team: $servicePlan->team);

        mock(CreatePlanRule::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (ServicePlan $servicePlanArgument, CreatePlanRuleInput $input): bool => $servicePlanArgument->is($servicePlan)
                && $input->countryCode === CountryCode::Canada)
            ->andReturn($createdPlanRule);

        $response = post(route('teams.service-plans.plan-rules.store', [
            'team' => $servicePlan->team,
            'service_plan' => $servicePlan,
        ]), [
            'billable_weight_rounding_mode' => BillableWeightRoundingMode::None->value,
            'country_code' => CountryCode::Canada->value,
            'currency_code' => CurrencyCode::USD->value,
            'minimum_billable_weight' => 1,
            'name' => 'Canada air cargo',
        ]);

        $response->assertRedirectToRoute('teams.service-plans.plan-rules.show', [
            'team' => $servicePlan->team,
            'service_plan' => $servicePlan,
            'plan_rule' => $createdPlanRule,
        ])
            ->assertToast('Plan rule created');
    });

    it('allows a value used by a soft deleted record', function (): void {
        $planRule = PlanRule::factory()
            ->trashed()
            ->createOne([
                'country_code' => CountryCode::Canada,
            ]);
        $createdPlanRule = PlanRule::factory()
            ->for($planRule->servicePlan)
            ->forCountry(CountryCode::Japan)
            ->createOne();

        signIn(team: $planRule->servicePlan->team);

        mock(CreatePlanRule::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (ServicePlan $servicePlan, CreatePlanRuleInput $input): bool => $servicePlan->is($planRule->servicePlan)
                && $input->countryCode === CountryCode::Canada)
            ->andReturn($createdPlanRule);

        $response = post(route('teams.service-plans.plan-rules.store', [
            'team' => $planRule->servicePlan->team,
            'service_plan' => $planRule->servicePlan,
        ]), [
            'billable_weight_rounding_mode' => BillableWeightRoundingMode::None->value,
            'country_code' => CountryCode::Canada->value,
            'currency_code' => CurrencyCode::USD->value,
            'minimum_billable_weight' => 1,
            'name' => 'Canada air cargo',
        ]);

        $response->assertRedirectToRoute('teams.service-plans.plan-rules.show', [
            'team' => $planRule->servicePlan->team,
            'service_plan' => $planRule->servicePlan,
            'plan_rule' => $createdPlanRule,
        ])
            ->assertToast('Plan rule created');
    });
});
```
