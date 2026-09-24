# Action Tests: Deactivate a Referenced Record

Integration test for allowing deactivation of a referenced group while preserving an existing item relationship. This assertion checks the relationship; it does not independently recheck the persisted deactivation timestamp.

```php
<?php

declare(strict_types=1);

use App\Actions\ItemGroups\DeactivateItemGroup;
use App\Models\ItemGroup;
use App\Models\WorkOrder;
use App\Models\WorkOrderLine;

it('deactivates a referenced group without breaking existing items', function (): void {
    $itemGroup = ItemGroup::factory()->createOne();
    $workOrder = WorkOrder::factory()
        ->recycle($itemGroup->team)
        ->createOne();
    $workOrderLine = WorkOrderLine::factory()
        ->recycle($workOrder)
        ->for($itemGroup, 'itemGroup')
        ->createOne();

    resolve(DeactivateItemGroup::class)->handle($itemGroup);

    expect($workOrderLine->itemGroup->is($itemGroup))->toBeTrue();
});
```
