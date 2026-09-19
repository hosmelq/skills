# Create Tests: Deactivated Parent 403 And Final-State Read-Only Pages

Use when a route parent's lifecycle affects an Inertia create form. Match the
current contract: a deactivated parent can forbid the GET with 403, while a final
parent can return 200 with `canMutate = false`. Do not turn the latter into a
forbidden response.

The ordered block places forbidden-parent checks after binding checks, before
the positive page test; final read-only variants follow the normal page/options
cases. These examples belong to separate controllers in a fictional workshop
application. Adapt existing factories, states, enums, routes and `signIn(team: ...)`.

## Deactivated Parent Forbids Viewing

For a plan-rule form directly under a service plan, use the same test name and
`assertForbidden()` with the direct parent route. This deeper example also passes
a valid rule, isolating the plan's deactivation.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\PlanRule;
use App\Models\ServicePlan;

describe('create', function (): void {
    it('prevents viewing when the service plan is deactivated', function (): void {
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

## Final Parent Remains Viewable

Keep the name `marks the page read only for final parent states`. Use a dataset when only the
final state changes. The positive mutable-parent test separately asserts
`canMutate = true`; do not replace that case with this dataset.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Enums\WorkOrderBaseStatus;
use App\Models\WorkOrder;
use App\Models\WorkOrderStatus;
use Inertia\Testing\AssertableInertia;

describe('create', function (): void {
    it('marks the page read only for final parent states', function (WorkOrderBaseStatus $baseStatus): void {
        $status = WorkOrderStatus::factory()->withBaseStatus($baseStatus)->createOne();
        $workOrder = WorkOrder::factory()->recycle($status->team)->for($status, 'workOrderStatus')->createOne();

        signIn(team: $workOrder->team);

        $response = get(route('teams.work-orders.items.create', [
            'team' => $workOrder->team,
            'work_order' => $workOrder,
        ]));

        $response->assertOk()
            ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
                ->component('work-orders/items/Create')
                ->where('canMutate', false));
    })->with([
        WorkOrderBaseStatus::Cancelled,
        WorkOrderBaseStatus::Completed,
        WorkOrderBaseStatus::Collected,
    ]);
});
```

## Related References

1. [Ordered create block](create.md)
2. [Scoped and soft-deleted parents](create-parent-scope.md)
3. [Mutable parent with eligible category options](create-option-lists.md)
