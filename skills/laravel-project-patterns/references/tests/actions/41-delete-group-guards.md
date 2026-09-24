# Action Tests: Delete Referenced Group Guards

Integration tests for group deletion: reject live items, an inactive group with live items and soft-deleted items; preserve the target on failure and soft delete an unreferenced group.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertNotSoftDeleted;
use function Pest\Laravel\assertSoftDeleted;

use App\Actions\ItemGroups\DeleteItemGroup;
use App\Exceptions\CannotDeleteItemGroup;
use App\Models\ItemGroup;
use App\Models\WorkOrder;
use App\Models\WorkOrderLine;

it('rejects deleting a group referenced by an active item', function (): void {
    $itemGroup = ItemGroup::factory()->createOne();
    $workOrder = WorkOrder::factory()
        ->recycle($itemGroup->team)
        ->createOne();

    WorkOrderLine::factory()
        ->recycle($workOrder)
        ->for($itemGroup, 'itemGroup')
        ->createOne();

    expect(fn () => resolve(DeleteItemGroup::class)
        ->handle($itemGroup))
        ->toThrow(
            CannotDeleteItemGroup::class,
            'Cannot delete an item group referenced by work order lines.',
        );

    assertNotSoftDeleted($itemGroup);
});

it('rejects deleting a deactivated group referenced by an active item', function (): void {
    $itemGroup = ItemGroup::factory()->deactivated()->createOne();
    $workOrder = WorkOrder::factory()
        ->recycle($itemGroup->team)
        ->createOne();

    WorkOrderLine::factory()
        ->recycle($workOrder)
        ->for($itemGroup, 'itemGroup')
        ->createOne();

    expect(fn () => resolve(DeleteItemGroup::class)->handle($itemGroup))
        ->toThrow(
            CannotDeleteItemGroup::class,
            'Cannot delete an item group referenced by work order lines.',
        );

    assertNotSoftDeleted($itemGroup);
});

it('rejects deleting a group referenced by a soft deleted item', function (): void {
    $itemGroup = ItemGroup::factory()->createOne();
    $workOrder = WorkOrder::factory()
        ->recycle($itemGroup->team)
        ->createOne();

    WorkOrderLine::factory()
        ->trashed()
        ->recycle($workOrder)
        ->for($itemGroup, 'itemGroup')
        ->createOne();

    expect(fn () => resolve(DeleteItemGroup::class)->handle($itemGroup))
        ->toThrow(
            CannotDeleteItemGroup::class,
            'Cannot delete an item group referenced by work order lines.',
        );

    assertNotSoftDeleted($itemGroup);
});

it('soft deletes a record', function (): void {
    $itemGroup = ItemGroup::factory()->createOne();

    resolve(DeleteItemGroup::class)->handle($itemGroup);

    assertSoftDeleted($itemGroup);
});
```
