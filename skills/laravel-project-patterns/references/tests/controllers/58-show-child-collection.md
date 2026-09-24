# Show Tests: Ordered Live Child Collection

Complete GET show detail returns exactly two live children oldest ID first, excluding foreign, deleted and unrelated children. Preserve the count prop, both IDs, populated and null group data, and two absent raw foreign keys on the first child.

Live here means not soft deleted. This example does not test an inactive-child rule.

## Child Collection

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\Team;
use App\Models\WorkOrder;
use App\Models\WorkOrderLine;
use Inertia\Testing\AssertableInertia;

describe('show', function (): void {
    it('lists live child records oldest first', function (
    ): void {
        $workOrder = WorkOrder::factory()->createOne();
        $firstLine = WorkOrderLine::factory()->withGroup()->recycle($workOrder)->createOne();
        $secondLine = WorkOrderLine::factory()->recycle($workOrder)->createOne();
        WorkOrderLine::factory()->for($workOrder)->for(Team::factory())->createOne();
        WorkOrderLine::factory()->trashed()->recycle($workOrder)->createOne();
        WorkOrderLine::factory()->createOne();

        login(team: $workOrder->team);

        $response = get(route('teams.work-orders.show', [
            'team' => $workOrder->team,
            'work_order' => $workOrder,
        ]));

        $response->assertOk()
            ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
                ->component('work-orders/Show')
                ->where('lineCount', 2)
                ->has('lines', 2)
                ->where('lines.0.id', $firstLine->sqid)
                ->where('lines.0.group.id', $firstLine->itemGroup->sqid)
                ->where('lines.0.group.name', $firstLine->itemGroup->name)
                ->where('lines.1.id', $secondLine->sqid)
                ->where('lines.1.group', null)
                ->missing('lines.0.work_order_id')
                ->missing('lines.0.item_group_id'));
    });
});
```
