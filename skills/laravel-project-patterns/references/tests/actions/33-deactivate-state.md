# Action Tests: Deactivate State Guards

Integration tests for state deactivation: reject the active initial state, deactivate an eligible state, preserve an existing reference and keep an already inactive timestamp unchanged.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Actions\WorkOrderStatuses\DeactivateWorkOrderStatus;
use App\Exceptions\CannotDeactivateWorkOrderStatus;
use App\Models\WorkOrder;
use App\Models\WorkOrderStatus;

it('rejects deactivating the active initial status', function (): void {
    $workOrderStatus = WorkOrderStatus::factory()->initial()->createOne();

    expect(fn () => resolve(DeactivateWorkOrderStatus::class)->handle($workOrderStatus))
        ->toThrow(
            CannotDeactivateWorkOrderStatus::class,
            'Cannot deactivate the active initial work order status.',
        );

    assertDatabaseHas(WorkOrderStatus::class, [
        'id' => $workOrderStatus->id,
        'deactivated_at' => null,
    ]);
});

it('deactivates an active non-initial status', function (): void {
    $workOrderStatus = WorkOrderStatus::factory()->createOne();

    resolve(DeactivateWorkOrderStatus::class)->handle($workOrderStatus);

    assertDatabaseHas(WorkOrderStatus::class, [
        'id' => $workOrderStatus->id,
        'deactivated_at' => now(),
    ]);
});

it('deactivates a non-initial status without breaking existing related records', function (): void {
    $workOrderStatus = WorkOrderStatus::factory()->createOne();
    $workOrder = WorkOrder::factory()
        ->recycle($workOrderStatus->team)
        ->recycle($workOrderStatus)
        ->createOne();

    resolve(DeactivateWorkOrderStatus::class)->handle($workOrderStatus);

    expect($workOrder->workOrderStatus->is($workOrderStatus))->toBeTrue();
});

it('deactivates a status without replacing its existing timestamp', function (): void {
    $deactivatedAt = now()->subDay()->startOfSecond();
    $workOrderStatus = WorkOrderStatus::factory()->deactivated()->createOne([
        'deactivated_at' => $deactivatedAt,
    ]);

    resolve(DeactivateWorkOrderStatus::class)->handle($workOrderStatus);

    assertDatabaseHas(WorkOrderStatus::class, [
        'id' => $workOrderStatus->id,
        'deactivated_at' => $deactivatedAt,
    ]);
});
```
