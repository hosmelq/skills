# Update Tests: Mapping Rate Stored Bounds

Pest PATCH update: Rate bound validation checks stored lower/upper values; separate successful cases explicitly clear the upper bound or update the lower bound against an already open-ended upper bound.

## Complete block

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\patch;

use App\Actions\ServicePlans\Inputs\UpdatePlanRateInput;
use App\Actions\ServicePlans\UpdatePlanRate;
use App\Models\PlanRate;
use Spatie\LaravelData\Optional;

describe('update', function (): void {
    it('validates an upper bound against the stored lower bound', function (): void {
        $rate = PlanRate::factory()
            ->forRange(10, 15)
            ->createOne();

        signIn(team: $rate->planRule->servicePlan->team);

        mock(UpdatePlanRate::class)
            ->shouldNotReceive('handle');

        $response = patch(route('teams.service-plans.plan-rules.rates.update', [
            'team' => $rate->planRule->servicePlan->team,
            'service_plan' => $rate->planRule->servicePlan,
            'plan_rule' => $rate->planRule,
            'rate' => $rate,
        ]), [
            'maximum_weight' => 5,
        ]);

        $response->assertRedirectBackWithErrors([
            'maximum_weight' => 'The maximum weight field must be greater than 10.0000.',
        ]);
    });

    it('validates a lower bound against the stored upper bound', function (): void {
        $rate = PlanRate::factory()
            ->forRange(5, 10)
            ->createOne();

        signIn(team: $rate->planRule->servicePlan->team);

        $response = patch(route('teams.service-plans.plan-rules.rates.update', [
            'team' => $rate->planRule->servicePlan->team,
            'service_plan' => $rate->planRule->servicePlan,
            'plan_rule' => $rate->planRule,
            'rate' => $rate,
        ]), [
            'minimum_weight' => 11,
        ]);

        $response->assertRedirectBackWithErrors([
            'maximum_weight' => 'The maximum weight field must be greater than 11.',
        ]);
    });

    it('maps a cleared upper bound with the stored lower bound', function (): void {
        $rate = PlanRate::factory()
            ->forRange(2, 10)
            ->createOne();

        signIn(team: $rate->planRule->servicePlan->team);

        mock(UpdatePlanRate::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (
                PlanRate $rateArgument,
                UpdatePlanRateInput $input,
            ): bool => $rateArgument->is($rate)
                && $input->maximumWeight === null
                && $input->minimumWeight === '2.0000');

        $response = patch(route('teams.service-plans.plan-rules.rates.update', [
            'team' => $rate->planRule->servicePlan->team,
            'service_plan' => $rate->planRule->servicePlan,
            'plan_rule' => $rate->planRule,
            'rate' => $rate,
        ]), [
            'maximum_weight' => null,
        ]);

        $response->assertRedirectToRoute('teams.service-plans.plan-rules.rates.show', [
            'team' => $rate->planRule->servicePlan->team,
            'service_plan' => $rate->planRule->servicePlan,
            'plan_rule' => $rate->planRule,
            'rate' => $rate,
        ])
            ->assertToast('Rate updated');
    });

    it('allows a lower bound update with an open-ended stored upper bound', function (): void {
        $rate = PlanRate::factory()
            ->forRange(5, null)
            ->createOne();

        signIn(team: $rate->planRule->servicePlan->team);

        mock(UpdatePlanRate::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (
                PlanRate $rateArgument,
                UpdatePlanRateInput $input,
            ): bool => $rateArgument->is($rate)
                && $input->minimumWeight === '11'
                && $input->maximumWeight instanceof Optional);

        $response = patch(route('teams.service-plans.plan-rules.rates.update', [
            'team' => $rate->planRule->servicePlan->team,
            'service_plan' => $rate->planRule->servicePlan,
            'plan_rule' => $rate->planRule,
            'rate' => $rate,
        ]), [
            'minimum_weight' => 11,
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
