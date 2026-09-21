# Update Tests: Uniqueness Rule Country

Pest PATCH update: Country uniqueness is scoped to the parent plan; retain current-record reuse, another parent scope and exact converted country input.

## Complete block

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\patch;

use App\Actions\ServicePlans\Inputs\UpdatePlanRuleInput;
use App\Actions\ServicePlans\UpdatePlanRule;
use App\Enums\CountryCode;
use App\Models\PlanRule;
use App\Models\ServicePlan;

describe('update', function (): void {
    it('rejects a duplicate value in the same scope', function (): void {
        $servicePlan = ServicePlan::factory()->createOne();
        PlanRule::factory()
            ->for($servicePlan)
            ->createOne(['country_code' => CountryCode::Canada]);
        $duplicatePlanRule = PlanRule::factory()
            ->for($servicePlan)
            ->createOne(['country_code' => CountryCode::Japan]);

        signIn(team: $servicePlan->team);

        $response = patch(route('teams.service-plans.plan-rules.update', [
            'team' => $servicePlan->team,
            'service_plan' => $servicePlan,
            'plan_rule' => $duplicatePlanRule,
        ]), [
            'country_code' => CountryCode::Canada->value,
        ]);

        $response->assertRedirectBackWithErrors([
            'country_code' => 'The country code has already been taken.',
        ]);
    });

    it('allows a value used in a different scope', function (): void {
        $existingPlanRule = PlanRule::factory()->createOne([
            'country_code' => CountryCode::Canada,
        ]);

        $servicePlan = ServicePlan::factory()
            ->for($existingPlanRule->servicePlan->team)
            ->createOne();
        $planRule = PlanRule::factory()
            ->for($servicePlan)
            ->createOne(['country_code' => CountryCode::Japan]);

        signIn(team: $servicePlan->team);

        mock(UpdatePlanRule::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (PlanRule $planRuleArgument, UpdatePlanRuleInput $input): bool => $planRuleArgument->is($planRule)
                && $input->countryCode === CountryCode::Canada);

        $response = patch(route('teams.service-plans.plan-rules.update', [
            'team' => $servicePlan->team,
            'service_plan' => $servicePlan,
            'plan_rule' => $planRule,
        ]), [
            'country_code' => CountryCode::Canada->value,
        ]);

        $response->assertRedirectToRoute('teams.service-plans.plan-rules.show', [
            'team' => $servicePlan->team,
            'service_plan' => $servicePlan,
            'plan_rule' => $planRule,
        ])
            ->assertToast('Plan rule updated');
    });

    it('allows the current value', function (): void {
        $planRule = PlanRule::factory()->createOne([
            'country_code' => CountryCode::Canada,
        ]);

        signIn(team: $planRule->servicePlan->team);

        mock(UpdatePlanRule::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (PlanRule $planRuleArgument, UpdatePlanRuleInput $input): bool => $planRuleArgument->is($planRule)
                && $input->countryCode === CountryCode::Canada);

        $response = patch(route('teams.service-plans.plan-rules.update', [
            'team' => $planRule->servicePlan->team,
            'service_plan' => $planRule->servicePlan,
            'plan_rule' => $planRule,
        ]), [
            'country_code' => CountryCode::Canada->value,
        ]);

        $response->assertRedirectToRoute('teams.service-plans.plan-rules.show', [
            'team' => $planRule->servicePlan->team,
            'service_plan' => $planRule->servicePlan,
            'plan_rule' => $planRule,
        ])
            ->assertToast('Plan rule updated');
    });
});
```
