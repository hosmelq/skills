# Actions: Create a Nested Record

Reject a final parent using its historical state, resolve a supplied optional group within the tenant’s active groups and create through the parent relation. Omitted/null group creates no assignment.

```php
<?php

declare(strict_types=1);

namespace App\Actions\WorkOrderLines;

use App\Actions\WorkOrderLines\Inputs\CreateWorkOrderLineInput;
use App\Exceptions\WorkOrderLines\ItemGroupIsUnavailable;
use App\Exceptions\WorkOrders\WorkOrderIsFinal;
use App\Models\ItemGroup;
use App\Models\WorkOrder;
use App\Models\WorkOrderLine;
use Spatie\LaravelData\Optional;

class CreateWorkOrderLine
{
    public function handle(WorkOrder $workOrder, CreateWorkOrderLineInput $input): WorkOrderLine
    {
        throw_if(
            $workOrder->workOrderStatus()
                ->withTrashed()
                ->firstOrFail()
                ->base_status
                ->isFinal(),
            WorkOrderIsFinal::becauseItCannotBeChangedOrDeleted(),
        );

        $itemGroupId = $input->itemGroupId instanceof Optional ? null : $input->itemGroupId;
        $itemGroup = $itemGroupId === null
            ? null
            : $this->resolveItemGroup($workOrder, $itemGroupId);

        return $this->create($workOrder, $input, $itemGroup);
    }

    private function create(
        WorkOrder $workOrder,
        CreateWorkOrderLineInput $input,
        null|ItemGroup $itemGroup = null,
    ): WorkOrderLine {
        $attributes = $input->transform();
        $attributes['item_group_id'] = $itemGroup?->id;

        return $workOrder->lines()->create($attributes);
    }

    private function resolveItemGroup(WorkOrder $workOrder, int $itemGroupId): ItemGroup
    {
        $itemGroup = $workOrder->team->itemGroups()
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
