# Action Tests: Delete State Guards

Integration tests for state deletion: reject an active initial state and live or soft-deleted references, including an inactive initial state with references; soft delete eligible states without selecting a replacement.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertNotSoftDeleted;
use function Pest\Laravel\assertSoftDeleted;

use App\Actions\WorkOrderStatuses\DeleteWorkOrderStatus;
use App\Exceptions\CannotDeleteInitialWorkOrderStatus;
use App\Exceptions\CannotDeleteWorkOrderStatus;
use App\Models\WorkOrder;
use App\Models\WorkOrderStatus;

it('rejects deleting the active initial status', function (): void {
    $workOrderStatus = WorkOrderStatus::factory()->initial()->createOne();

    expect(fn () => resolve(DeleteWorkOrderStatus::class)->handle($workOrderStatus))
        ->toThrow(
            CannotDeleteInitialWorkOrderStatus::class,
            'The team must keep an active initial work order status.',
        );

    assertNotSoftDeleted($workOrderStatus);
});

it('rejects deleting a status referenced by an active related record', function (): void {
    $workOrder = WorkOrder::factory()->createOne();

    expect(fn () => resolve(DeleteWorkOrderStatus::class)->handle($workOrder->workOrderStatus))
        ->toThrow(
            CannotDeleteWorkOrderStatus::class,
            'This work order status is assigned to one or more work orders and cannot be deleted.',
        );

    assertNotSoftDeleted($workOrder->workOrderStatus);
});

it('rejects deleting a status referenced by a soft deleted related record', function (): void {
    $workOrder = WorkOrder::factory()->trashed()->createOne();
    $workOrderStatus = $workOrder->workOrderStatus;

    expect(fn () => resolve(DeleteWorkOrderStatus::class)->handle($workOrderStatus))
        ->toThrow(
            CannotDeleteWorkOrderStatus::class,
            'This work order status is assigned to one or more work orders and cannot be deleted.',
        );

    assertNotSoftDeleted($workOrderStatus);
});

it('rejects deleting a deactivated initial status referenced by a related record', function (): void {
    $workOrderStatus = WorkOrderStatus::factory()->initial()->deactivated()->createOne();
    WorkOrder::factory()
        ->recycle($workOrderStatus->team)
        ->recycle($workOrderStatus)
        ->createOne();

    expect(fn () => resolve(DeleteWorkOrderStatus::class)->handle($workOrderStatus))
        ->toThrow(
            CannotDeleteWorkOrderStatus::class,
            'This work order status is assigned to one or more work orders and cannot be deleted.',
        );

    assertNotSoftDeleted($workOrderStatus);
});

it('soft deletes a record', function (): void {
    $workOrderStatus = WorkOrderStatus::factory()->createOne();

    resolve(DeleteWorkOrderStatus::class)->handle($workOrderStatus);

    assertSoftDeleted($workOrderStatus);
});

it('soft deletes a deactivated initial status without selecting a replacement', function (): void {
    $workOrderStatus = WorkOrderStatus::factory()->initial()->deactivated()->createOne();
    $replacementCandidate = WorkOrderStatus::factory()
        ->recycle($workOrderStatus->team)
        ->createOne();

    resolve(DeleteWorkOrderStatus::class)->handle($workOrderStatus);

    assertSoftDeleted($workOrderStatus);
    assertDatabaseHas(WorkOrderStatus::class, [
        'id' => $replacementCandidate->id,
        'is_initial' => false,
    ]);
});
```
