# Action Tests: Update State Guards

Integration action tests: Reject updates to final records across all final enum states and a soft-deleted historical state; preserve the existing value.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Actions\WorkOrders\Inputs\UpdateWorkOrderInput;
use App\Actions\WorkOrders\UpdateWorkOrder;
use App\Enums\BaseStatus;
use App\Exceptions\WorkOrders\WorkOrderIsFinal;
use App\Models\WorkOrder;
use App\Models\WorkOrderStatus;

it('rejects a final record', function (BaseStatus $baseStatus): void {
    $status = WorkOrderStatus::factory()->withBaseStatus($baseStatus)->createOne();
    $workOrder = WorkOrder::factory()
        ->recycle($status->team)
        ->for($status, 'workOrderStatus')
        ->createOne(['note' => 'Original note']);

    expect(fn () => resolve(UpdateWorkOrder::class)->handle($workOrder, UpdateWorkOrderInput::from([
        'note' => 'Cannot update',
    ])))->toThrow(
        WorkOrderIsFinal::class,
        'Work orders in a final status cannot be changed or deleted.',
    );

    assertDatabaseHas(WorkOrder::class, [
        'id' => $workOrder->id,
        'note' => 'Original note',
    ]);
})->with([
    BaseStatus::Cancelled,
    BaseStatus::Completed,
    BaseStatus::Archived,
]);

it('rejects a final record with a historical status', function (): void {
    $status = WorkOrderStatus::factory()
        ->trashed()
        ->withBaseStatus(BaseStatus::Completed)
        ->createOne();
    $workOrder = WorkOrder::factory()
        ->recycle($status->team)
        ->for($status, 'workOrderStatus')
        ->createOne(['note' => 'Original note']);

    expect(fn () => resolve(UpdateWorkOrder::class)->handle($workOrder, UpdateWorkOrderInput::from([
        'note' => 'Cannot update',
    ])))->toThrow(
        WorkOrderIsFinal::class,
        'Work orders in a final status cannot be changed or deleted.',
    );

    assertDatabaseHas(WorkOrder::class, [
        'id' => $workOrder->id,
        'note' => 'Original note',
    ]);
});
```
