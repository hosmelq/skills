# Action Tests: Delete Active Children Only

Integration action tests: Soft delete a parent and active children, preserve the original deletion timestamp of historical children, and preserve other parents and their children.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertNotSoftDeleted;
use function Pest\Laravel\assertSoftDeleted;

use App\Actions\WorkOrders\DeleteWorkOrder;
use App\Models\WorkOrder;
use App\Models\WorkOrderLine;

it('soft deletes a record and its active children', function (): void {
    $workOrder = WorkOrder::factory()->createOne();
    $activeLines = WorkOrderLine::factory()->count(2)->recycle($workOrder)->create();
    $historicalLine = WorkOrderLine::factory()->trashed()->recycle($workOrder)->createOne();
    $historicalDeletedAt = $historicalLine->deleted_at;

    resolve(DeleteWorkOrder::class)->handle($workOrder);

    assertSoftDeleted($workOrder);
    $activeLines->each(fn (WorkOrderLine $line) => assertSoftDeleted($line));

    assertDatabaseHas(WorkOrderLine::class, [
        'id' => $historicalLine->id,
        'deleted_at' => $historicalDeletedAt,
    ]);
});

it('preserves unrelated parents and children', function (): void {
    $workOrder = WorkOrder::factory()->createOne();
    $otherWorkOrder = WorkOrder::factory()->recycle($workOrder->team)->createOne();
    $otherLine = WorkOrderLine::factory()->recycle($otherWorkOrder)->createOne();

    resolve(DeleteWorkOrder::class)->handle($workOrder);

    assertNotSoftDeleted($otherLine);
    assertNotSoftDeleted($otherWorkOrder);
});
```
