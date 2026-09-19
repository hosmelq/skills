# Create Tests: Access Denied For An Inactive Parent

Use after authentication, tenant authorization and route-binding checks, before
the successful page case. A live but inactive route parent returns HTTP 403 when the
policy denies opening the form; a soft-deleted parent has a separate HTTP 404 case.

The fictional example binds a service plan and its rule. For a form directly under
the plan, use the same test name and assertion with only that parent parameter.
Factories, routes and `signIn(team: ...)` are illustrative project contracts.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\PlanRule;
use App\Models\ServicePlan;

describe('create', function (): void {
    it('prevents viewing when the parent is inactive', function (): void {
        $planRule = PlanRule::factory()
            ->for(ServicePlan::factory()->deactivated())
            ->createOne();

        signIn(team: $planRule->servicePlan->team);

        $response = get(route('teams.service-plans.plan-rules.rates.create', [
            'team' => $planRule->servicePlan->team,
            'service_plan' => $planRule->servicePlan,
            'plan_rule' => $planRule,
        ]));

        $response->assertForbidden();
    });
});
```

## Related References

- [Ordered create block](00-create-test-order.md)
- [Route binding and soft deletes](01-create-route-bindings.md)
- [A final parent that remains viewable](08-create-read-only.md)
