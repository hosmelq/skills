# Actions: Move Between Ordered Groups

When a supplied enum changes the group, reset the order during update, normalize the old non-deleted group, append to the new group and refresh. Unchanged groups return without reordering.

The model uses Spatie Sortable with tenant/group scope and `sort_order`. `setNewOrder()` removes the soft-delete scope internally; retain the explicit deleted_at filter in modifyQuery.

```php
<?php

declare(strict_types=1);

namespace App\Actions\WorkOrderStatuses;

use App\Actions\WorkOrderStatuses\Inputs\UpdateWorkOrderStatusInput;
use App\Enums\BaseStatus;
use App\Models\WorkOrderStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class UpdateWorkOrderStatus
{
    public function handle(
        WorkOrderStatus $workOrderStatus,
        UpdateWorkOrderStatusInput $input,
    ): WorkOrderStatus {
        return DB::transaction(function () use ($input, $workOrderStatus): WorkOrderStatus {
            $previousBaseStatus = $workOrderStatus->base_status;
            $baseStatusChanged = $input->baseStatus instanceof BaseStatus
                && $input->baseStatus !== $previousBaseStatus;

            $attributes = $input->transform();

            if ($baseStatusChanged) {
                $attributes['sort_order'] = 0;
            }

            $workOrderStatus->update($attributes);

            if (! $baseStatusChanged) {
                return $workOrderStatus;
            }

            $this->normalizeGroupOrder($workOrderStatus->team_id, $previousBaseStatus);

            $workOrderStatus->setHighestOrderNumber();
            $workOrderStatus->save();

            return $workOrderStatus->refresh();
        });
    }

    private function normalizeGroupOrder(int $teamId, BaseStatus $baseStatus): void
    {
        $orderedIds = WorkOrderStatus::query()
            ->where('team_id', $teamId)
            ->where('base_status', $baseStatus)
            ->ordered()
            ->pluck('id');

        WorkOrderStatus::setNewOrder(
            $orderedIds,
            modifyQuery: fn (Builder $query): Builder => $query
                ->where('team_id', $teamId)
                ->where('base_status', $baseStatus)
                ->whereNull('deleted_at'),
        );
    }
}
```
