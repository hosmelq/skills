# Update Tests: Mapping Rate Full And Partial

Pest PATCH update: Complete and name-only rate updates preserve exact action identity, the asserted DTO fields, redirects and toast. The name-only example asserts name without checking omitted DTO properties.

## Complete block

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\patch;

use App\Actions\ServicePlans\Inputs\UpdatePlanRateInput;
use App\Actions\ServicePlans\UpdatePlanRate;
use App\Models\PlanRate;

describe('update', function (): void {
    it('updates the record', function (): void {
        $rate = PlanRate::factory()->createOne();

        signIn(team: $rate->planRule->servicePlan->team);

        mock(UpdatePlanRate::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (
                PlanRate $rateArgument,
                UpdatePlanRateInput $input,
            ): bool => $rateArgument->is($rate)
                && $input->name === 'Updated Rate'
                && $input->minimumWeight === '1'
                && $input->maximumWeight === '10'
                && $input->rate === '25.99');

        $response = patch(route('teams.service-plans.plan-rules.rates.update', [
            'team' => $rate->planRule->servicePlan->team,
            'service_plan' => $rate->planRule->servicePlan,
            'plan_rule' => $rate->planRule,
            'rate' => $rate,
        ]), [
            'name' => 'Updated Rate',
            'minimum_weight' => 1,
            'maximum_weight' => 10,
            'rate' => 25.99,
        ]);

        $response->assertRedirectToRoute('teams.service-plans.plan-rules.rates.show', [
            'team' => $rate->planRule->servicePlan->team,
            'service_plan' => $rate->planRule->servicePlan,
            'plan_rule' => $rate->planRule,
            'rate' => $rate,
        ])
            ->assertToast('Rate updated');
    });

    it('maps a name-only update to the action', function (): void {
        $rate = PlanRate::factory()
            ->forRange(0, 2)
            ->createOne();

        signIn(team: $rate->planRule->servicePlan->team);

        mock(UpdatePlanRate::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (
                PlanRate $rateArgument,
                UpdatePlanRateInput $input,
            ): bool => $rateArgument->is($rate)
                && $input->name === 'Updated without range conflict');

        $response = patch(route('teams.service-plans.plan-rules.rates.update', [
            'team' => $rate->planRule->servicePlan->team,
            'service_plan' => $rate->planRule->servicePlan,
            'plan_rule' => $rate->planRule,
            'rate' => $rate,
        ]), [
            'name' => 'Updated without range conflict',
        ]);

        $response->assertRedirectToRoute('teams.service-plans.plan-rules.rates.show', [
            'team' => $rate->planRule->servicePlan->team,
            'service_plan' => $rate->planRule->servicePlan,
            'plan_rule' => $rate->planRule,
            'rate' => $rate,
        ])
            ->assertToast('Rate updated');
    });
});
```
