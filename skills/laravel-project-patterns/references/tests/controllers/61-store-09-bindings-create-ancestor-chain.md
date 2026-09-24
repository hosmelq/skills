# Store Tests: Bindings Create Ancestor Chain

POST store: Three bindings: ancestor foreign/deleted, parent foreign/deleted, or parent attached to another ancestor in the same tenant.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\post;

use App\Models\PlanRule;
use App\Models\ServicePlan;
use App\Models\Team;

describe('store', function (): void {
    it('returns not found when the ancestor belongs to another tenant', function (): void {
        $team = Team::factory()->createOne();
        $planRule = PlanRule::factory()->createOne();

        login(team: $team);

        $response = post(route('teams.service-plans.plan-rules.rates.store', [
            'team' => $team,
            'service_plan' => $planRule->servicePlan,
            'plan_rule' => $planRule,
        ]));

        $response->assertNotFound();
    });

    it('returns not found when the ancestor is soft deleted', function (): void {
        $servicePlan = ServicePlan::factory()->trashed()->createOne();
        $planRule = PlanRule::factory()->recycle($servicePlan)->createOne();

        login(team: $servicePlan->team);

        $response = post(route('teams.service-plans.plan-rules.rates.store', [
            'team' => $servicePlan->team,
            'service_plan' => $servicePlan,
            'plan_rule' => $planRule,
        ]));

        $response->assertNotFound();
    });

    it('returns not found when the parent belongs to another ancestor in the same tenant', function (): void {
        $servicePlan = ServicePlan::factory()->createOne();
        $planRule = PlanRule::factory()->recycle($servicePlan->team)->createOne();

        login(team: $servicePlan->team);

        $response = post(route('teams.service-plans.plan-rules.rates.store', [
            'team' => $servicePlan->team,
            'service_plan' => $servicePlan,
            'plan_rule' => $planRule,
        ]));

        $response->assertNotFound();
    });

    it('returns not found when the parent belongs to another tenant', function (): void {
        $servicePlan = ServicePlan::factory()->createOne();
        $unrelatedPlanRule = PlanRule::factory()->createOne();

        login(team: $servicePlan->team);

        $response = post(route('teams.service-plans.plan-rules.rates.store', [
            'team' => $servicePlan->team,
            'service_plan' => $servicePlan,
            'plan_rule' => $unrelatedPlanRule,
        ]));

        $response->assertNotFound();
    });

    it('returns not found when the parent is soft deleted', function (): void {
        $planRule = PlanRule::factory()->trashed()->createOne();

        login(team: $planRule->servicePlan->team);

        $response = post(route('teams.service-plans.plan-rules.rates.store', [
            'team' => $planRule->servicePlan->team,
            'service_plan' => $planRule->servicePlan,
            'plan_rule' => $planRule,
        ]));

        $response->assertNotFound();
    });
});
```
