# Update Tests: Bindings Deep Record

Pest PATCH update: A deeply nested record belongs to a sibling parent, a different ancestor in the same tenant, another tenant, or is soft deleted. Distinct countries keep the sibling-parent fixture valid.

## Complete block

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\patch;

use App\Actions\ServicePlans\UpdatePlanRate;
use App\Enums\CountryCode;
use App\Models\PlanRate;
use App\Models\PlanRule;

describe('update', function (): void {
    it('returns not found when the record belongs to another parent under the same ancestor', function (): void {
        $planRule = PlanRule::factory()
            ->forCountry(CountryCode::Canada)
            ->createOne();
        $rate = PlanRate::factory()
            ->for($planRule, 'planRule')
            ->createOne();
        $unrelatedPlanRule = PlanRule::factory()
            ->for($planRule->servicePlan)
            ->forCountry(CountryCode::Japan)
            ->createOne();
        $unrelatedRate = PlanRate::factory()
            ->for($unrelatedPlanRule, 'planRule')
            ->createOne();

        signIn(team: $rate->planRule->servicePlan->team);

        $response = patch(route('teams.service-plans.plan-rules.rates.update', [
            'team' => $rate->planRule->servicePlan->team,
            'service_plan' => $rate->planRule->servicePlan,
            'plan_rule' => $rate->planRule,
            'rate' => $unrelatedRate,
        ]));

        $response->assertNotFound();
    });

    it('returns not found when the record belongs to another ancestor in the same tenant', function (): void {
        $rate = PlanRate::factory()->createOne();
        $unrelatedRate = PlanRate::factory()
            ->recycle($rate->planRule->servicePlan->team)
            ->createOne();

        signIn(team: $rate->planRule->servicePlan->team);

        $response = patch(route('teams.service-plans.plan-rules.rates.update', [
            'team' => $rate->planRule->servicePlan->team,
            'service_plan' => $rate->planRule->servicePlan,
            'plan_rule' => $rate->planRule,
            'rate' => $unrelatedRate,
        ]));

        $response->assertNotFound();
    });

    it('returns not found when the record belongs to another tenant', function (): void {
        $planRule = PlanRule::factory()->createOne();
        $unrelatedRate = PlanRate::factory()->createOne();

        signIn(team: $planRule->servicePlan->team);

        $response = patch(route('teams.service-plans.plan-rules.rates.update', [
            'team' => $planRule->servicePlan->team,
            'service_plan' => $planRule->servicePlan,
            'plan_rule' => $planRule,
            'rate' => $unrelatedRate,
        ]));

        $response->assertNotFound();
    });

    it('returns not found when the record is soft deleted', function (): void {
        $rate = PlanRate::factory()->trashed()->createOne();

        signIn(team: $rate->planRule->servicePlan->team);

        mock(UpdatePlanRate::class)
            ->shouldNotReceive('handle');

        $response = patch(route('teams.service-plans.plan-rules.rates.update', [
            'team' => $rate->planRule->servicePlan->team,
            'service_plan' => $rate->planRule->servicePlan,
            'plan_rule' => $rate->planRule,
            'rate' => $rate,
        ]));

        $response->assertNotFound();
    });
});
```
