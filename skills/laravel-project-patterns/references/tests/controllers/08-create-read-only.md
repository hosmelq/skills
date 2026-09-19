# Create Tests: Read-Only Page For Final Parent States

Use after the normal create-page/options cases when a final parent still permits
viewing. Assert HTTP 200 and `canMutate = false` for each supported final state.
The normal page test separately asserts `canMutate = true`; do not replace either
case with a 403 assertion.

Use `marks the page read only for final parent states` across controllers. The
dataset varies only the parent state; request and assertions stay identical.
Enums, factory states, relationships, routes and `signIn(team: ...)` below belong
to a fictional workshop; adapt them to the actual project.

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

- [Ordered create block](00-create-test-order.md)
- [Mutable page and eligible options](04-create-select-options.md)
- [An inactive parent that forbids viewing](02-create-inactive-parent.md)
