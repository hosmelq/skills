# Action Tests: Update Child Group Selection

Integration action tests: Reject newly assigned foreign-tenant, inactive or trashed groups; preserve the current inactive group for both explicitly unchanged and omitted input.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Actions\WorkOrderLines\Inputs\UpdateWorkOrderLineInput;
use App\Actions\WorkOrderLines\UpdateWorkOrderLine;
use App\Exceptions\WorkOrderLines\ItemGroupIsUnavailable;
use App\Models\ItemGroup;
use App\Models\WorkOrder;
use App\Models\WorkOrderLine;

it('rejects a newly assigned unavailable group', function (string $state): void {
    $line = WorkOrderLine::factory()->createOne();
    $factory = ItemGroup::factory();

    if ($state !== 'another team') {
        $factory = $factory->for($line->workOrder->team);
    }

    $itemGroup = match ($state) {
        'deactivated' => $factory->deactivated()->createOne(),
        'soft deleted' => $factory->trashed()->createOne(),
        default => $factory->createOne(),
    };

    expect(fn () => resolve(UpdateWorkOrderLine::class)->handle(
        $line,
        UpdateWorkOrderLineInput::from([
            'item_group_id' => $itemGroup->id,
        ]),
    ))->toThrow(
        ItemGroupIsUnavailable::class,
        'The selected item group is unavailable.',
    );

    assertDatabaseHas(WorkOrderLine::class, [
        'id' => $line->id,
        'item_group_id' => null,
    ]);
})->with([
    'another team',
    'deactivated',
    'soft deleted',
]);

it('preserves the current inactive group', function (bool $submitGroup): void {
    $itemGroup = ItemGroup::factory()->deactivated()->createOne();
    $workOrder = WorkOrder::factory()->for($itemGroup->team)->createOne();
    $line = WorkOrderLine::factory()
        ->recycle($workOrder)
        ->for($itemGroup, 'itemGroup')
        ->createOne(['description' => 'Before']);
    $payload = ['description' => 'After'];

    if ($submitGroup) {
        $payload['item_group_id'] = $itemGroup->id;
    }

    resolve(UpdateWorkOrderLine::class)->handle(
        $line,
        UpdateWorkOrderLineInput::from($payload),
    );

    assertDatabaseHas(WorkOrderLine::class, [
        'id' => $line->id,
        'item_group_id' => $itemGroup->id,
        'description' => 'After',
    ]);
})->with([
    'explicitly unchanged' => true,
    'omitted' => false,
]);
```
