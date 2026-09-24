# Store Tests: Failures Final Parent And Group

Pest POST store: Final parent and subsequently unavailable selected group; separate guards and error fields with a valid original request.

The action is mocked to throw. These assertions verify controller translation, not the underlying business guard.

## Complete block

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\post;

use App\Actions\WorkOrderLines\CreateWorkOrderLine;
use App\Exceptions\WorkOrderLines\ItemGroupIsUnavailable;
use App\Exceptions\WorkOrders\WorkOrderIsFinal;
use App\Models\ItemGroup;
use App\Models\WorkOrder;

describe('store', function (): void {
    it('maps an unavailable relation rejection to validation', function (): void {
        $workOrder = WorkOrder::factory()->createOne();
        $group = ItemGroup::factory()->recycle($workOrder->team)->createOne();

        login(team: $workOrder->team);

        mock(CreateWorkOrderLine::class)
            ->shouldReceive('handle')
            ->once()
            ->andThrow(ItemGroupIsUnavailable::becauseItIsUnavailable());

        $response = post(route('teams.work-orders.lines.store', [
            'team' => $workOrder->team,
            'work_order' => $workOrder,
        ]), [
            'description' => 'Laptop computer',
            'item_group_id' => $group->public_id,
            'quantity' => 2,
        ]);

        $response->assertRedirectBackWithErrors([
            'item_group_id' =>
                'The selected item group is unavailable.',
        ]);
    });

    it('maps a final parent rejection to validation', function (): void {
        $workOrder = WorkOrder::factory()->createOne();

        login(team: $workOrder->team);

        mock(CreateWorkOrderLine::class)
            ->shouldReceive('handle')
            ->once()
            ->andThrow(WorkOrderIsFinal::becauseItCannotBeChangedOrDeleted());

        $response = post(route('teams.work-orders.lines.store', [
            'team' => $workOrder->team,
            'work_order' => $workOrder,
        ]), [
            'description' => 'Laptop computer',
            'quantity' => 2,
        ]);

        $response->assertRedirectBackWithErrors([
            'work_order' =>
                'Work order lines cannot be created after the work order reaches a final status.',
        ]);
    });
});
```
