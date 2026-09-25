# Action Tests: Create Child Guards

Integration action tests: Reject final parent states, a historical soft-deleted final state, and unavailable groups across tenant, inactive and trashed cases; assert no child was inserted.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseCount;

use App\Actions\WorkOrderLines\CreateWorkOrderLine;
use App\Actions\WorkOrderLines\Inputs\CreateWorkOrderLineInput;
use App\Enums\BaseStatus;
use App\Exceptions\WorkOrderLines\ItemGroupIsUnavailable;
use App\Exceptions\WorkOrders\WorkOrderIsFinal;
use App\Models\ItemGroup;
use App\Models\Team;
use App\Models\WorkOrder;
use App\Models\WorkOrderLine;
use App\Models\WorkOrderStatus;

it('rejects a final parent', function (BaseStatus $baseStatus): void {
    $team = Team::factory()->createOne();
    $workOrderStatus = WorkOrderStatus::factory()
        ->recycle($team)
        ->withBaseStatus($baseStatus)
        ->createOne();
    $workOrder = WorkOrder::factory()->for($team)->for($workOrderStatus)->createOne();

    expect(fn () => resolve(CreateWorkOrderLine::class)->handle(
        $workOrder,
        CreateWorkOrderLineInput::from([
            'description' => 'Documents',
            'quantity' => 1,
        ]),
    ))->toThrow(
        WorkOrderIsFinal::class,
        'Work orders in a final status cannot be changed or deleted.',
    );

    assertDatabaseCount(WorkOrderLine::class, 0);
})->with([
    BaseStatus::Cancelled,
    BaseStatus::Completed,
    BaseStatus::Archived,
]);

it('rejects a final parent with a historical status', function (): void {
    $team = Team::factory()->createOne();
    $workOrderStatus = WorkOrderStatus::factory()
        ->trashed()
        ->recycle($team)
        ->withBaseStatus(BaseStatus::Completed)
        ->createOne();
    $workOrder = WorkOrder::factory()->for($team)->for($workOrderStatus)->createOne();

    expect(fn () => resolve(CreateWorkOrderLine::class)->handle(
        $workOrder,
        CreateWorkOrderLineInput::from([
            'description' => 'Documents',
            'quantity' => 1,
        ]),
    ))->toThrow(
        WorkOrderIsFinal::class,
        'Work orders in a final status cannot be changed or deleted.',
    );

    assertDatabaseCount(WorkOrderLine::class, 0);
});

it('rejects an unavailable group', function (string $state): void {
    $workOrder = WorkOrder::factory()->createOne();
    $factory = ItemGroup::factory();

    if ($state !== 'another team') {
        $factory = $factory->for($workOrder->team);
    }

    $itemGroup = match ($state) {
        'deactivated' => $factory->deactivated()->createOne(),
        'soft deleted' => $factory->trashed()->createOne(),
        default => $factory->createOne(),
    };

    expect(fn () => resolve(CreateWorkOrderLine::class)->handle(
        $workOrder,
        CreateWorkOrderLineInput::from([
            'description' => 'Documents',
            'item_group_id' => $itemGroup->id,
            'quantity' => 1,
        ]),
    ))->toThrow(
        ItemGroupIsUnavailable::class,
        'The selected item group is unavailable.',
    );

    assertDatabaseCount(WorkOrderLine::class, 0);
})->with([
    'another team',
    'deactivated',
    'soft deleted',
]);
```
