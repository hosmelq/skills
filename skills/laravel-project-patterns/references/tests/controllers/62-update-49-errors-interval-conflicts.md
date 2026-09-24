# Update Tests: Errors Interval Conflicts

PATCH update: Mocked second-open-ended and overlap rejections preserve the edited rate's stored bounds and exact validation errors. These tests do not create a separate conflicting interval.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\patch;

use App\Actions\ServicePlans\Inputs\UpdatePlanRateInput;
use App\Actions\ServicePlans\UpdatePlanRate;
use App\Exceptions\CannotUpdatePlanRate;
use App\Models\PlanRate;

describe('update', function (): void {
    it('maps a second open-ended range rejection to validation', function (): void {
        $rate = PlanRate::factory()
            ->forRange(0, 5)
            ->createOne();

        login(team: $rate->planRule->servicePlan->team);

        mock(UpdatePlanRate::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (
                PlanRate $rateArgument,
                UpdatePlanRateInput $input,
            ): bool => $rateArgument->is($rate)
                && $input->maximumWeight === null)
            ->andThrow(CannotUpdatePlanRate::becauseItHasAnOpenEndedRate());

        $response = patch(route('teams.service-plans.plan-rules.rates.update', [
            'team' => $rate->planRule->servicePlan->team,
            'service_plan' => $rate->planRule->servicePlan,
            'plan_rule' => $rate->planRule,
            'rate' => $rate,
        ]), [
            'maximum_weight' => null,
        ]);

        $response->assertRedirectBackWithErrors([
            'maximum_weight' => 'Only one open-ended rate is allowed per plan rule.',
        ]);
    });

    it('maps an overlapping range rejection to validation', function (): void {
        $rate = PlanRate::factory()
            ->forRange(3, 5)
            ->createOne();

        login(team: $rate->planRule->servicePlan->team);

        mock(UpdatePlanRate::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (
                PlanRate $rateArgument,
                UpdatePlanRateInput $input,
            ): bool => $rateArgument->is($rate)
                && $input->maximumWeight === '4'
                && $input->minimumWeight === '1')
            ->andThrow(CannotUpdatePlanRate::becauseItOverlapsAnExistingRate());

        $response = patch(route('teams.service-plans.plan-rules.rates.update', [
            'team' => $rate->planRule->servicePlan->team,
            'service_plan' => $rate->planRule->servicePlan,
            'plan_rule' => $rate->planRule,
            'rate' => $rate,
        ]), [
            'minimum_weight' => '1',
            'maximum_weight' => '4',
        ]);

        $response->assertRedirectBackWithErrors([
            'minimum_weight' => 'The weight range overlaps an existing rate.',
        ]);
    });
});
```
