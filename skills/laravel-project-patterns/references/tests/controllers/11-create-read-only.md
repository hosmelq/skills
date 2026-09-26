# Create Tests: Read-Only Page For Final Parent States

Final parent states remain viewable: the create page returns HTTP 200 with mutation disabled. A dataset covers each supported final state after the positive page cases; this is distinct from forbidden access.

The normal page case separately asserts `canMutate = true`. Vary only the parent state in this dataset.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Enums\BaseStatus;
use App\Models\WorkOrder;
use App\Models\WorkOrderStatus;
use Inertia\Testing\AssertableInertia;

describe('create', function (): void {
    it('marks the page read only for final parent states', function (BaseStatus $baseStatus): void {
        $status = WorkOrderStatus::factory()->withBaseStatus($baseStatus)->createOne();
        $workOrder = WorkOrder::factory()
            ->recycle($status->team)
            ->for($status, 'workOrderStatus')
            ->createOne();

        login(team: $workOrder->team);

        $response = get(route('teams.work-orders.lines.create', [
            'team' => $workOrder->team,
            'work_order' => $workOrder,
        ]));

        $response->assertOk()
            ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
                ->component('work-orders/lines/Create')
                ->where('canMutate', false));
    })->with([
        BaseStatus::Cancelled,
        BaseStatus::Completed,
        BaseStatus::Archived,
    ]);
});
```
