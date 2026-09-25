# Action Tests: Delete a Child Record

Integration action tests: Reject final and historical-final parents; delete only the selected child and preserve its sibling.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertNotSoftDeleted;
use function Pest\Laravel\assertSoftDeleted;

use App\Actions\WorkOrderLines\DeleteWorkOrderLine;
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
    $line = WorkOrderLine::factory()->recycle($workOrder)->createOne();

    expect(fn () => resolve(DeleteWorkOrderLine::class)->handle($line))
        ->toThrow(
            WorkOrderIsFinal::class,
            'Work orders in a final status cannot be changed or deleted.',
        );

    assertNotSoftDeleted($line);
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
        ->withBaseStatus(BaseStatus::Archived)
        ->createOne();
    $workOrder = WorkOrder::factory()->for($team)->for($workOrderStatus)->createOne();
    $line = WorkOrderLine::factory()->recycle($workOrder)->createOne();

    expect(fn () => resolve(DeleteWorkOrderLine::class)->handle($line))
        ->toThrow(
            WorkOrderIsFinal::class,
            'Work orders in a final status cannot be changed or deleted.',
        );

    assertNotSoftDeleted($line);
});

it('soft deletes a record and preserves siblings', function (): void {
    $workOrder = WorkOrder::factory()->createOne();
    $line = WorkOrderLine::factory()->recycle($workOrder)->createOne();
    $otherLine = WorkOrderLine::factory()->recycle($workOrder)->createOne();

    resolve(DeleteWorkOrderLine::class)->handle($line);

    assertSoftDeleted($line);
    assertNotSoftDeleted($otherLine);
});
```
