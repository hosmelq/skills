# Create Tests: Scoped Nested Parent Binding And Soft Deletes

Use when a Laravel GET `create` route binds one or more nested parents under a
team. Place these checks after authentication and team authorization, before
lifecycle restrictions and the positive page test. Sign in to the team in the URL
so a 403 cannot mask incorrect route binding.

## Binding Order

1. Outer parent belongs to a different team: 404.
2. Outer parent is soft deleted: 404.
3. Child belongs to a different parent in the same team: 404.
4. Child belongs to a different team: 404.
5. Child is soft deleted: 404.

The same-team case needs two different parents. A foreign-team fixture cannot
prove that the immediate parent is scoped correctly. Delete only the targeted
route level in each soft-delete test.

The fictional route below is service plan → plan rule → new rate. For member →
new address/enrollment or work order → new item routes, apply the corresponding
outer-parent checks when the live binding contract matches, and substitute the
parent in the test name. Extra nesting adds checks, not a new naming convention.

## Complete Nested Binding Examples

Factories, `recycle()`, `trashed()`, the team-aware `signIn()` helper and route
parameters are illustrative application contracts. Here recycling a team creates
a different service plan in that same team. Check the actual factory relationships.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\PlanRule;
use App\Models\ServicePlan;
use App\Models\Team;

describe('create', function (): void {
    it('returns not found when service plan belongs to a different team', function (): void {
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

    it('returns not found when service plan is soft deleted', function (): void {
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

    it('returns not found when plan rule belongs to a different service plan in the same team', function (): void {
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

    it('returns not found when plan rule belongs to a different team', function (): void {
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

    it('returns not found when plan rule is soft deleted', function (): void {
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

## Related References

1. [Ordered create block: redirect and unauthorized team 403](create.md)
2. [Deactivated parent and final parent states](create-parent-state.md)
