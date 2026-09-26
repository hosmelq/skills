# Actions: Ensure an Initial State

Return an existing eligible initial state, promote the first eligible ordered state, or create a translated default. Preserve this precedence and the caller transaction.

The translation helper returns a string. Model defaults/casts and the selection helper provide the inspected state contract; the read/create sequence alone does not prove race safety.

```php
<?php

declare(strict_types=1);

namespace App\Actions\WorkOrderStatuses;

use function App\__;

use App\Enums\BaseStatus;
use App\Models\Team;
use App\Models\WorkOrderStatus;
use Illuminate\Support\Facades\DB;

class EnsureInitialWorkOrderStatus
{
    public function __construct(
        private readonly SetInitialWorkOrderStatus $setInitialWorkOrderStatus,
    ) {
    }

    public function handle(Team $team): WorkOrderStatus
    {
        return DB::transaction(function () use ($team): WorkOrderStatus {
            $initialWorkOrderStatus = $team->workOrderStatuses()
                ->where('is_initial', true)
                ->where('base_status', BaseStatus::Open)
                ->whereNull('deactivated_at')
                ->first();

            if ($initialWorkOrderStatus instanceof WorkOrderStatus) {
                return $initialWorkOrderStatus;
            }

            $openWorkOrderStatus = $team->workOrderStatuses()
                ->where('base_status', BaseStatus::Open)
                ->whereNull('deactivated_at')
                ->ordered()
                ->first();

            if ($openWorkOrderStatus instanceof WorkOrderStatus) {
                return $this->setInitialWorkOrderStatus->handle($openWorkOrderStatus);
            }

            return $team->workOrderStatuses()->create([
                'base_status' => BaseStatus::Open,
                'color' => null,
                'description' => null,
                'is_initial' => true,
                'is_member_visible' => false,
                'name' => __('work_order_status.defaults.initial_name'),
            ]);
        });
    }
}
```
