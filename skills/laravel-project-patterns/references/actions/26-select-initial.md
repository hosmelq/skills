# Actions: Select an Initial State

Require an eligible state, clear prior tenant selections including trashed rows, select the target and refresh it within one transaction.

```php
<?php

declare(strict_types=1);

namespace App\Actions\WorkOrderStatuses;

use App\Exceptions\CannotSetInitialWorkOrderStatus;
use App\Models\WorkOrderStatus;
use Illuminate\Support\Facades\DB;

class SetInitialWorkOrderStatus
{
    public function handle(WorkOrderStatus $workOrderStatus): WorkOrderStatus
    {
        return DB::transaction(function () use ($workOrderStatus): WorkOrderStatus {
            if (! $workOrderStatus->canBeInitial()) {
                throw CannotSetInitialWorkOrderStatus::becauseItIsNotActiveOpen();
            }

            WorkOrderStatus::query()
                ->withTrashed()
                ->where('team_id', $workOrderStatus->team_id)
                ->where('is_initial', true)
                ->update(['is_initial' => false]);

            $workOrderStatus->update(['is_initial' => true]);

            return $workOrderStatus->refresh();
        });
    }
}
```
