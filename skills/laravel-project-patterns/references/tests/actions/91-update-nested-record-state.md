# Action Tests: Update Child State Guards

Integration action tests: Reject final parent states and a historical final state; also reject after the database parent state changes while the child holds a stale loaded relation.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Actions\WorkOrderLines\Inputs\UpdateWorkOrderLineInput;
use App\Actions\WorkOrderLines\UpdateWorkOrderLine;
use App\Enums\BaseStatus;
use App\Exceptions\WorkOrders\WorkOrderIsFinal;
use App\Models\Team;
use App\Models\WorkOrder;
use App\Models\WorkOrderLine;
use App\Models\WorkOrderStatus;

it('rejects a final parent', function (BaseStatus $baseStatus): void {
    $team = Team::factory()->createOne();
    $workOrderStatus = WorkOrderStatus::factory()
        ->recycle($team)
        ->withBaseStatus($baseStatus)
        ->createOne();
    $workOrder = WorkOrder::factory()->for($team)->for($workOrderStatus)->createOne();
    $line = WorkOrderLine::factory()->recycle($workOrder)->createOne(['description' => 'Before']);

    expect(fn () => resolve(UpdateWorkOrderLine::class)->handle(
        $line,
        UpdateWorkOrderLineInput::from(['description' => 'After']),
    ))->toThrow(
        WorkOrderIsFinal::class,
        'Work orders in a final status cannot be changed or deleted.',
    );

    assertDatabaseHas(WorkOrderLine::class, [
        'id' => $line->id,
        'description' => 'Before',
    ]);
})->with([
    BaseStatus::Cancelled,
    BaseStatus::Completed,
    BaseStatus::Archived,
]);

it('rejects a final parent with a historical status', function (): void {
    $team = Team::factory()->createOne();
    $workOrderStatus = WorkOrderStatus::factory()
        ->trashed()
        ->recycle($team)
        ->withBaseStatus(BaseStatus::Completed)
        ->createOne();
    $workOrder = WorkOrder::factory()->for($team)->for($workOrderStatus)->createOne();
    $line = WorkOrderLine::factory()->recycle($workOrder)->createOne(['description' => 'Before']);

    expect(fn () => resolve(UpdateWorkOrderLine::class)->handle(
        $line,
        UpdateWorkOrderLineInput::from(['description' => 'After']),
    ))->toThrow(
        WorkOrderIsFinal::class,
        'Work orders in a final status cannot be changed or deleted.',
    );

    assertDatabaseHas(WorkOrderLine::class, [
        'id' => $line->id,
        'description' => 'Before',
    ]);
});

it('rejects a newly final parent despite a stale loaded relation', function (): void {
    $team = Team::factory()->createOne();
    $receivedStatus = WorkOrderStatus::factory()->recycle($team)->createOne();
    $finalStatus = WorkOrderStatus::factory()
        ->recycle($team)
        ->withBaseStatus(BaseStatus::Completed)
        ->createOne();
    $workOrder = WorkOrder::factory()->for($team)->for($receivedStatus)->createOne();
    $line = WorkOrderLine::factory()->recycle($workOrder)->createOne(['description' => 'Before']);

    $workOrder->load('workOrderStatus');
    $line->setRelation('workOrder', $workOrder);
    WorkOrder::query()->whereKey($workOrder)->update([
        'work_order_status_id' => $finalStatus->id,
    ]);

    expect(fn () => resolve(UpdateWorkOrderLine::class)->handle(
        $line,
        UpdateWorkOrderLineInput::from(['description' => 'After']),
    ))->toThrow(
        WorkOrderIsFinal::class,
        'Work orders in a final status cannot be changed or deleted.',
    );

    assertDatabaseHas(WorkOrderLine::class, [
        'id' => $line->id,
        'description' => 'Before',
    ]);
});
```
