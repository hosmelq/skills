# Create Tests: Scoped Nested Parent Binding And Soft Deletes

Scoped binding tests for nested create routes: foreign-tenant parents, a child under the wrong same-tenant parent, and soft-deleted parents must return HTTP 404. Includes outer and nested parent cases in order.

Authorize the tenant in the URL so 403 cannot mask broken binding. Use two distinct parents for the same-tenant case; delete only the targeted level for each soft-delete case.

For a single bound parent, keep the first two cases and omit child parameters. Place binding tests after authentication/authorization and before lifecycle restrictions.

## Complete Nested Binding Examples

Here `recycle($servicePlan->team)` creates a different plan in the same team; verify the actual factory relationships.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\PlanRule;
use App\Models\ServicePlan;
use App\Models\Team;

describe('create', function (): void {
    it('returns not found when the parent belongs to another tenant', function (): void {
        $team = Team::factory()->createOne();
        $planRule = PlanRule::factory()->createOne();

        signIn(team: $team);

        $response = get(route('teams.service-plans.plan-rules.rates.create', [
            'team' => $team,
            'service_plan' => $planRule->servicePlan,
            'plan_rule' => $planRule,
        ]));

        $response->assertNotFound();
    });

    it('returns not found when the parent is soft deleted', function (): void {
        $servicePlan = ServicePlan::factory()->trashed()->createOne();
        $planRule = PlanRule::factory()
            ->for($servicePlan)
            ->createOne();

        signIn(team: $servicePlan->team);

        $response = get(route('teams.service-plans.plan-rules.rates.create', [
            'team' => $servicePlan->team,
            'service_plan' => $servicePlan,
            'plan_rule' => $planRule,
        ]));

        $response->assertNotFound();
    });

    it('returns not found when the nested parent belongs to another parent in the same tenant', function (): void {
        $servicePlan = ServicePlan::factory()->createOne();
        $planRule = PlanRule::factory()
            ->recycle($servicePlan->team)
            ->createOne();

        signIn(team: $servicePlan->team);

        $response = get(route('teams.service-plans.plan-rules.rates.create', [
            'team' => $servicePlan->team,
            'service_plan' => $servicePlan,
            'plan_rule' => $planRule,
        ]));

        $response->assertNotFound();
    });

    it('returns not found when the nested parent belongs to another tenant', function (): void {
        $servicePlan = ServicePlan::factory()->createOne();
        $unrelatedPlanRule = PlanRule::factory()->createOne();

        signIn(team: $servicePlan->team);

        $response = get(route('teams.service-plans.plan-rules.rates.create', [
            'team' => $servicePlan->team,
            'service_plan' => $servicePlan,
            'plan_rule' => $unrelatedPlanRule,
        ]));

        $response->assertNotFound();
    });

    it('returns not found when the nested parent is soft deleted', function (): void {
        $planRule = PlanRule::factory()->trashed()->createOne();

        signIn(team: $planRule->servicePlan->team);

        $response = get(route('teams.service-plans.plan-rules.rates.create', [
            'team' => $planRule->servicePlan->team,
            'service_plan' => $planRule->servicePlan,
            'plan_rule' => $planRule,
        ]));

        $response->assertNotFound();
    });
});
```
