# Edit Tests: Final Record Flag And Read Only Parent States

Viewable GET edit final-state contracts: a final record exposes its nested status is_final=true; a child page returns canMutate=false for three final parent states. Both return HTTP 200 and the exact component. The record flag alone does not assert mutation permissions.

Keep the single-state record case separate from the parent-state dataset. Preserve the inspected enum cases; neither example tests persistence or submission.

## Record Final Flag

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Enums\WorkOrderBaseStatus;
use App\Models\WorkOrder;
use App\Models\WorkOrderStatus;
use Inertia\Testing\AssertableInertia;

describe('edit', function (): void {
    it('exposes the record final state', function (): void {
        $status = WorkOrderStatus::factory()
            ->withBaseStatus(WorkOrderBaseStatus::Completed)
            ->createOne();
        $workOrder = WorkOrder::factory()->recycle($status->team)->for($status, 'workOrderStatus')->createOne();

        login(team: $workOrder->team);

        $response = get(route('teams.work-orders.edit', [
            'team' => $workOrder->team,
            'work_order' => $workOrder,
        ]));

        $response->assertOk()
            ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
                ->component('work-orders/Edit')
                ->where('workOrder.status.is_final', true));
    });
});
```

## Final Parent Dataset

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Enums\WorkOrderBaseStatus;
use App\Models\WorkOrder;
use App\Models\WorkOrderLine;
use App\Models\WorkOrderStatus;
use Inertia\Testing\AssertableInertia;

describe('edit', function (): void {
    it('marks the page read only for final parent states', function (WorkOrderBaseStatus $baseStatus): void {
        $status = WorkOrderStatus::factory()->withBaseStatus($baseStatus)->createOne();
        $workOrder = WorkOrder::factory()->recycle($status->team)->for($status, 'workOrderStatus')->createOne();
        $line = WorkOrderLine::factory()->recycle($workOrder)->createOne();

        login(team: $workOrder->team);

        $response = get(route('teams.work-orders.lines.edit', [
            'team' => $workOrder->team,
            'work_order' => $workOrder,
            'line' => $line,
        ]));

        $response->assertOk()
            ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
                ->component('work-orders/lines/Edit')
                ->where('canMutate', false));
    })->with([
        WorkOrderBaseStatus::Cancelled,
        WorkOrderBaseStatus::Completed,
        WorkOrderBaseStatus::Collected,
    ]);
});
```
