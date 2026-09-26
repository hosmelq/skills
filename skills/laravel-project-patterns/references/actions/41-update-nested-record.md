# Actions: Update a Nested Relation

Reject a final parent, then distinguish omitted, null, unchanged and replacement group IDs. An unchanged deactivated group is preserved; the current-group lookup uses the default soft-delete scope. Resolve replacements among active tenant groups.

```php
<?php

declare(strict_types=1);

namespace App\Actions\WorkOrderLines;

use App\Actions\WorkOrderLines\Inputs\UpdateWorkOrderLineInput;
use App\Exceptions\WorkOrderLines\ItemGroupIsUnavailable;
use App\Exceptions\WorkOrders\WorkOrderIsFinal;
use App\Models\ItemGroup;
use App\Models\WorkOrderLine;
use Spatie\LaravelData\Optional;

class UpdateWorkOrderLine
{
    public function handle(WorkOrderLine $line, UpdateWorkOrderLineInput $input): WorkOrderLine
    {
        $workOrder = $line->workOrder()->firstOrFail();

        throw_if(
            $workOrder->workOrderStatus()->withTrashed()->firstOrFail()->base_status->isFinal(),
            WorkOrderIsFinal::becauseItCannotBeChangedOrDeleted(),
        );

        $attributes = $input->transform();
        unset($attributes['item_group_id']);

        if ($input->itemGroupId instanceof Optional) {
            return tap($line)->update($attributes);
        }

        if ($input->itemGroupId === null) {
            $attributes['item_group_id'] = null;

            return tap($line)->update($attributes);
        }

        $currentItemGroupId = $line->itemGroup?->id;

        if ($input->itemGroupId === $currentItemGroupId) {
            return tap($line)->update($attributes);
        }

        $itemGroup = $this->resolveItemGroup($line, $input->itemGroupId);
        $attributes['item_group_id'] = $itemGroup->id;

        return tap($line)->update($attributes);
    }

    private function resolveItemGroup(WorkOrderLine $line, int $itemGroupId): ItemGroup
    {
        $itemGroup = $line->team->itemGroups()
            ->active()
            ->whereKey($itemGroupId)
            ->first();

        throw_unless(
            $itemGroup instanceof ItemGroup,
            ItemGroupIsUnavailable::becauseItIsUnavailable(),
        );

        return $itemGroup;
    }
}
```
