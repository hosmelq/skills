# Action Tests: Roll Back Child Deletion

Integration action tests: Raise an exception from the parent deleting event; assert child deletions roll back and the parent remains active.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertNotSoftDeleted;

use App\Actions\WorkOrders\DeleteWorkOrder;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\Event;

it('rolls back child deletion when parent deletion fails', function (): void {
    $workOrder = WorkOrder::factory()->withLine()->createOne();
    $line = $workOrder->lines->sole();

    Event::listen('eloquent.deleting: '.WorkOrder::class, function (): never {
        throw new RuntimeException('Unexpected delete failure.');
    });

    expect(fn () => resolve(DeleteWorkOrder::class)->handle($workOrder))
        ->toThrow(RuntimeException::class, 'Unexpected delete failure.');

    assertNotSoftDeleted($workOrder);
    assertNotSoftDeleted($line);
});
```
