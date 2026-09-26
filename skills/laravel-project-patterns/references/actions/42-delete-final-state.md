# Actions: Delete Under a Mutable State

Reject final historical parent state before deleting. Root deletion wraps child bulk cleanup and root deletion in a transaction; deleting a single child has no action-level transaction.

```php
<?php

declare(strict_types=1);

namespace App\Actions\WorkOrders;

use App\Exceptions\WorkOrders\WorkOrderIsFinal;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\DB;

class DeleteWorkOrder
{
    public function handle(WorkOrder $workOrder): void
    {
        DB::transaction(function () use ($workOrder): void {
            throw_if(
                $workOrder->workOrderStatus()
                    ->withTrashed()
                    ->firstOrFail()
                    ->base_status
                    ->isFinal(),
                WorkOrderIsFinal::becauseItCannotBeChangedOrDeleted(),
            );

            $workOrder->lines()->delete();
            $workOrder->delete();
        });
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Actions\WorkOrderLines;

use App\Exceptions\WorkOrders\WorkOrderIsFinal;
use App\Models\WorkOrderLine;

class DeleteWorkOrderLine
{
    public function handle(WorkOrderLine $line): void
    {
        $workOrder = $line->workOrder()->firstOrFail();

        throw_if(
            $workOrder->workOrderStatus()
                ->withTrashed()
                ->firstOrFail()
                ->base_status
                ->isFinal(),
            WorkOrderIsFinal::becauseItCannotBeChangedOrDeleted(),
        );

        $line->delete();
    }
}
```
