# Create Tests: Access Denied For An Inactive Parent

Access restriction for a create form whose live parent is inactive: HTTP 403 before the positive page case. Distinct from a soft-deleted parent returning 404 and a viewable final parent returning 200.

Place after authentication, authorization and binding checks. For a form directly under the plan, keep the same name and assertion with only that parent parameter.

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
