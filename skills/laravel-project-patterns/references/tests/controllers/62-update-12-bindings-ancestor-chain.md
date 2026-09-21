# Update Tests: Bindings Ancestor Chain

Pest PATCH update: Two-parent routes: ancestor foreign/deleted, parent under another same-tenant ancestor, parent foreign/deleted. Preserve every live descendant.

## Complete block

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\patch;

use App\Models\PlanRate;
use App\Models\PlanRule;
use App\Models\ServicePlan;
use App\Models\Team;

describe('update', function (): void {
    it('returns not found when the ancestor belongs to another tenant', function (): void {
        $team = Team::factory()->createOne();
        $rate = PlanRate::factory()->createOne();

        signIn(team: $team);

        $response = patch(route('teams.service-plans.plan-rules.rates.update', [
            'team' => $team,
            'service_plan' => $rate->planRule->servicePlan,
            'plan_rule' => $rate->planRule,
            'rate' => $rate,
        ]));

        $response->assertNotFound();
    });

    it('returns not found when the ancestor is soft deleted', function (): void {
        $servicePlan = ServicePlan::factory()->trashed()->createOne();
        $planRule = PlanRule::factory()
            ->for($servicePlan)
            ->createOne();
        $rate = PlanRate::factory()
            ->for($planRule, 'planRule')
            ->createOne();

        signIn(team: $servicePlan->team);

        $response = patch(route('teams.service-plans.plan-rules.rates.update', [
            'team' => $servicePlan->team,
            'service_plan' => $servicePlan,
            'plan_rule' => $planRule,
            'rate' => $rate,
        ]));

        $response->assertNotFound();
    });

    it('returns not found when the parent belongs to another ancestor in the same tenant', function (): void {
        $servicePlan = ServicePlan::factory()->createOne();
        $planRule = PlanRule::factory()
            ->recycle($servicePlan->team)
            ->createOne();
        $rate = PlanRate::factory()
            ->for($planRule, 'planRule')
            ->createOne();

        signIn(team: $servicePlan->team);

        $response = patch(route('teams.service-plans.plan-rules.rates.update', [
            'team' => $servicePlan->team,
            'service_plan' => $servicePlan,
            'plan_rule' => $planRule,
            'rate' => $rate,
        ]));

        $response->assertNotFound();
    });

    it('returns not found when the parent belongs to another tenant', function (): void {
        $rate = PlanRate::factory()->createOne();
        $unrelatedPlanRule = PlanRule::factory()->createOne();

        signIn(team: $rate->planRule->servicePlan->team);

        $response = patch(route('teams.service-plans.plan-rules.rates.update', [
            'team' => $rate->planRule->servicePlan->team,
            'service_plan' => $rate->planRule->servicePlan,
            'plan_rule' => $unrelatedPlanRule,
            'rate' => $rate,
        ]));

        $response->assertNotFound();
    });

    it('returns not found when the parent is soft deleted', function (): void {
        $planRule = PlanRule::factory()->trashed()->createOne();
        $rate = PlanRate::factory()
            ->for($planRule, 'planRule')
            ->createOne();

        signIn(team: $planRule->servicePlan->team);

        $response = patch(route('teams.service-plans.plan-rules.rates.update', [
            'team' => $planRule->servicePlan->team,
            'service_plan' => $planRule->servicePlan,
            'plan_rule' => $planRule,
            'rate' => $rate,
        ]));

        $response->assertNotFound();
    });
});
```
