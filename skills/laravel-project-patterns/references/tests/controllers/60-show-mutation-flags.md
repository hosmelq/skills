# Show Tests: Parent Finality And Mutation Flags

Complete GET child show examples distinguish a nonfinal parent, three final-state dataset variants and a historical final state. Preserve canMutate, tested canDelete absence, relation data and raw foreign-key omissions without inferring write-action validation.

The historical example asserts canMutate=false but does not assert canDelete absence. Keep that boundary when adapting it.

## Nonfinal Parent

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\WorkOrderLine;
use Inertia\Testing\AssertableInertia;

describe('show', function (): void {
    it('exposes mutation availability for a nonfinal parent', function (): void {
        $line = WorkOrderLine::factory()->withGroup()->createOne();

        login(team: $line->workOrder->team);

        $response = get(route('teams.work-orders.lines.show', [
            'team' => $line->workOrder->team,
            'work_order' => $line->workOrder,
            'line' => $line,
        ]));

        $response->assertOk()
            ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
                ->component('work-orders/lines/Show')
                ->where('team.id', $line->workOrder->team->sqid)
                ->where('workOrder.id', $line->workOrder->sqid)
                ->where('line.id', $line->sqid)
                ->where('line.group.id', $line->itemGroup->sqid)
                ->where('line.group.name', $line->itemGroup->name)
                ->where('canMutate', true)
                ->missing('canDelete')
                ->missing('line.work_order_id')
                ->missing('line.item_group_id'));
    });
});
```

## Final Parent States

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Enums\WorkOrderBaseStatus;
use App\Models\WorkOrder;
use App\Models\WorkOrderLine;
use App\Models\WorkOrderStatus;
use Inertia\Testing\AssertableInertia;

describe('show', function (): void {
    it('marks the page read only for final parent states', function (
        WorkOrderBaseStatus $baseStatus,
    ): void {
        $status = WorkOrderStatus::factory()->withBaseStatus($baseStatus)->createOne();
        $workOrder = WorkOrder::factory()->recycle($status->team)->for($status, 'workOrderStatus')->createOne();
        $line = WorkOrderLine::factory()->recycle($workOrder)->createOne();

        login(team: $workOrder->team);

        $response = get(route('teams.work-orders.lines.show', [
            'team' => $workOrder->team,
            'work_order' => $workOrder,
            'line' => $line,
        ]));

        $response->assertOk()
            ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
                ->component('work-orders/lines/Show')
                ->where('canMutate', false)
                ->missing('canDelete'));
    })->with([
        WorkOrderBaseStatus::Cancelled,
        WorkOrderBaseStatus::Completed,
        WorkOrderBaseStatus::Collected,
    ]);
});
```

## Historical Final Parent State

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Enums\WorkOrderBaseStatus;
use App\Models\Team;
use App\Models\WorkOrder;
use App\Models\WorkOrderLine;
use App\Models\WorkOrderStatus;
use Inertia\Testing\AssertableInertia;

describe('show', function (): void {
    it('marks the page read only for a historical final parent state', function (): void {
        $team = Team::factory()->createOne();
        $workOrderStatus = WorkOrderStatus::factory()
            ->trashed()
            ->recycle($team)
            ->withBaseStatus(WorkOrderBaseStatus::Collected)
            ->createOne();
        $workOrder = WorkOrder::factory()
            ->recycle($team)
            ->for($workOrderStatus)
            ->createOne();
        $line = WorkOrderLine::factory()->recycle($workOrder)->createOne();

        login(team: $team);

        $response = get(route('teams.work-orders.lines.show', [
            'team' => $workOrder->team,
            'work_order' => $workOrder,
            'line' => $line,
        ]));

        $response->assertOk()
            ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
                ->component('work-orders/lines/Show')
                ->where('line.id', $line->sqid)
                ->where('canMutate', false));
    });
});
```
