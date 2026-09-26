# Actions: Initial-State Lifecycle

Protect an active initial state from deactivation, leave already-inactive timestamps unchanged, and clear the initial flag on reactivation. These actions use direct updates instead of generic model lifecycle helpers.

```php
<?php

declare(strict_types=1);

namespace App\Actions\WorkOrderStatuses;

use App\Exceptions\CannotDeactivateWorkOrderStatus;
use App\Models\WorkOrderStatus;

class DeactivateWorkOrderStatus
{
    public function handle(WorkOrderStatus $workOrderStatus): void
    {
        if ($workOrderStatus->is_initial && $workOrderStatus->isActive()) {
            throw CannotDeactivateWorkOrderStatus::becauseItIsActiveInitial();
        }

        if ($workOrderStatus->deactivated_at === null) {
            $workOrderStatus->update(['deactivated_at' => now()]);
        }
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Actions\WorkOrderStatuses;

use App\Models\WorkOrderStatus;

class ReactivateWorkOrderStatus
{
    public function handle(WorkOrderStatus $workOrderStatus): void
    {
        $workOrderStatus->update([
            'deactivated_at' => null,
            'is_initial' => false,
        ]);
    }
}
```
