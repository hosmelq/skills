# Store Tests: Mapping Optional Bound

POST store: Two complete success cases preserve omitted Optional versus explicit null upper bound and decimal strings.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\post;

use App\Actions\ServicePlans\CreatePlanRate;
use App\Actions\ServicePlans\Inputs\CreatePlanRateInput;
use App\Models\PlanRate;
use App\Models\PlanRule;
use Spatie\LaravelData\Optional;

describe('store', function (): void {
    it('stores the record with omitted optional fields', function (): void {
        $planRule = PlanRule::factory()->createOne();
        $rate = PlanRate::factory()->recycle($planRule)->createOne();

        login(team: $planRule->servicePlan->team);

        mock(CreatePlanRate::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (PlanRule $planRuleArgument, CreatePlanRateInput $input): bool => $planRuleArgument->is($planRule)
                && $input->maximumWeight instanceof Optional
                && $input->minimumWeight === '0.5'
                && $input->name === 'Standard Rate'
                && $input->rate === '12.5')
            ->andReturn($rate);

        $response = post(route('teams.service-plans.plan-rules.rates.store', [
            'team' => $planRule->servicePlan->team,
            'service_plan' => $planRule->servicePlan,
            'plan_rule' => $planRule,
        ]), [
            'minimum_weight' => '0.5',
            'name' => 'Standard Rate',
            'rate' => '12.5',
        ]);

        $response->assertRedirectToRoute('teams.service-plans.plan-rules.rates.show', [
            'team' => $planRule->servicePlan->team,
            'service_plan' => $planRule->servicePlan,
            'plan_rule' => $planRule,
            'rate' => $rate,
        ])
            ->assertToast('Rate created');
    });

    it('stores the record with an explicit null upper bound', function (): void {
        $planRule = PlanRule::factory()->createOne();
        $rate = PlanRate::factory()->recycle($planRule)->createOne();

        login(team: $planRule->servicePlan->team);

        mock(CreatePlanRate::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (PlanRule $planRuleArgument, CreatePlanRateInput $input): bool => $planRuleArgument->is($planRule)
                && $input->maximumWeight === null
                && $input->minimumWeight === '0.5'
                && $input->name === 'Open Ended Rate'
                && $input->rate === '12.5')
            ->andReturn($rate);

        $response = post(route('teams.service-plans.plan-rules.rates.store', [
            'team' => $planRule->servicePlan->team,
            'service_plan' => $planRule->servicePlan,
            'plan_rule' => $planRule,
        ]), [
            'minimum_weight' => '0.5',
            'maximum_weight' => null,
            'name' => 'Open Ended Rate',
            'rate' => '12.5',
        ]);

        $response->assertRedirectToRoute('teams.service-plans.plan-rules.rates.show', [
            'team' => $planRule->servicePlan->team,
            'service_plan' => $planRule->servicePlan,
            'plan_rule' => $planRule,
            'rate' => $rate,
        ])
            ->assertToast('Rate created');
    });
});
```
