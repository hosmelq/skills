# Actions: Guard Historical References

Reject deletion when dependent history exists, including soft-deleted rows. A protected active initial state adds an earlier guard; keep the failure order.

```php
<?php

declare(strict_types=1);

namespace App\Actions\ItemGroups;

use App\Exceptions\CannotDeleteItemGroup;
use App\Models\ItemGroup;

class DeleteItemGroup
{
    public function handle(ItemGroup $itemGroup): void
    {
        throw_if(
            $itemGroup->workOrderLines()->withTrashed()->exists(),
            CannotDeleteItemGroup::class,
        );

        $itemGroup->delete();
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Actions\WorkOrderStatuses;

use App\Exceptions\CannotDeleteInitialWorkOrderStatus;
use App\Exceptions\CannotDeleteWorkOrderStatus;
use App\Models\WorkOrderStatus;

class DeleteWorkOrderStatus
{
    public function handle(WorkOrderStatus $workOrderStatus): void
    {
        throw_if(
            $workOrderStatus->is_initial && $workOrderStatus->isActive(),
            CannotDeleteInitialWorkOrderStatus::class,
        );

        throw_if(
            $workOrderStatus->workOrders()->withTrashed()->exists(),
            CannotDeleteWorkOrderStatus::class,
        );

        $workOrderStatus->delete();
    }
}
```
