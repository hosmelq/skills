# Action Tests: Delete State Guards

Integration action tests: Reject soft deletion of final records and historical final states; preserve both parent and child rows.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertNotSoftDeleted;

use App\Actions\WorkOrders\DeleteWorkOrder;
use App\Enums\BaseStatus;
use App\Exceptions\WorkOrders\WorkOrderIsFinal;
use App\Models\WorkOrder;
use App\Models\WorkOrderStatus;

it('rejects a final record', function (BaseStatus $baseStatus): void {
    $status = WorkOrderStatus::factory()->withBaseStatus($baseStatus)->createOne();
    $workOrder = WorkOrder::factory()
        ->recycle($status->team)
        ->for($status, 'workOrderStatus')
        ->withLine()
        ->createOne();

    expect(fn () => resolve(DeleteWorkOrder::class)->handle($workOrder))
        ->toThrow(
            WorkOrderIsFinal::class,
            'Work orders in a final status cannot be changed or deleted.',
        );

    assertNotSoftDeleted($workOrder);
    assertNotSoftDeleted($workOrder->lines->sole());
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
        ->withLine()
        ->createOne();

    expect(fn () => resolve(DeleteWorkOrder::class)->handle($workOrder))
        ->toThrow(
            WorkOrderIsFinal::class,
            'Work orders in a final status cannot be changed or deleted.',
        );

    assertNotSoftDeleted($workOrder);
    assertNotSoftDeleted($workOrder->lines->sole());
});
```
