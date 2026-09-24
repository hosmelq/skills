# Action Tests: Reactivate Without Selecting

Integration test for reactivating a formerly initial state: clear inactivity and its stale initial flag. A repeated call preserves the resulting state.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Actions\WorkOrderStatuses\ReactivateWorkOrderStatus;
use App\Models\WorkOrderStatus;

it('reactivates a status without making it initial', function (): void {
    $workOrderStatus = WorkOrderStatus::factory()->initial()->deactivated()->createOne();

    $reactivateWorkOrderStatus = resolve(ReactivateWorkOrderStatus::class);

    $reactivateWorkOrderStatus->handle($workOrderStatus);
    $reactivateWorkOrderStatus->handle($workOrderStatus);

    assertDatabaseHas(WorkOrderStatus::class, [
        'id' => $workOrderStatus->id,
        'deactivated_at' => null,
        'is_initial' => false,
    ]);
});
```
