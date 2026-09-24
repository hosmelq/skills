# Store Tests: Failures Range Collision

POST store: Mocked exceptions test validation mapping only. Overlap vs second open-ended interval: different input bounds, exceptions and error fields.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\post;

use App\Actions\ServicePlans\CreatePlanRate;
use App\Actions\ServicePlans\Inputs\CreatePlanRateInput;
use App\Exceptions\CannotCreatePlanRate;
use App\Models\PlanRule;

describe('store', function (): void {
    it('maps an overlapping range rejection to validation', function (): void {
        $planRule = PlanRule::factory()->createOne();

        login(team: $planRule->servicePlan->team);

        mock(CreatePlanRate::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (PlanRule $planRuleArgument, CreatePlanRateInput $input): bool => $planRuleArgument->is($planRule)
                && $input->maximumWeight === '3'
                && $input->minimumWeight === '1'
                && $input->name === 'Standard Rate'
                && $input->rate === '12.5')
            ->andThrow(CannotCreatePlanRate::becauseItOverlapsAnExistingRate());

        $response = post(route('teams.service-plans.plan-rules.rates.store', [
            'team' => $planRule->servicePlan->team,
            'service_plan' => $planRule->servicePlan,
            'plan_rule' => $planRule,
        ]), [
            'minimum_weight' => '1',
            'maximum_weight' => '3',
            'name' => 'Standard Rate',
            'rate' => '12.5',
        ]);

        $response->assertRedirectBackWithErrors([
            'minimum_weight' => 'The weight range overlaps an existing rate.',
        ]);
    });

    it('maps a second open-ended range rejection to validation', function (): void {
        $planRule = PlanRule::factory()->createOne();

        login(team: $planRule->servicePlan->team);

        mock(CreatePlanRate::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (PlanRule $planRuleArgument, CreatePlanRateInput $input): bool => $planRuleArgument->is($planRule)
                && $input->maximumWeight === null
                && $input->minimumWeight === '10'
                && $input->name === 'Standard Rate'
                && $input->rate === '12.5')
            ->andThrow(CannotCreatePlanRate::becauseItHasAnOpenEndedRate());

        $response = post(route('teams.service-plans.plan-rules.rates.store', [
            'team' => $planRule->servicePlan->team,
            'service_plan' => $planRule->servicePlan,
            'plan_rule' => $planRule,
        ]), [
            'minimum_weight' => '10',
            'maximum_weight' => null,
            'name' => 'Standard Rate',
            'rate' => '12.5',
        ]);

        $response->assertRedirectBackWithErrors([
            'maximum_weight' => 'Only one open-ended rate is allowed per plan rule.',
        ]);
    });
});
```
