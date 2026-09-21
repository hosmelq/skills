# Store Tests: Relations Item Group

Pest POST store: Selected group invalid public ID, foreign tenant, inactive or deleted with valid base payload.

## Complete block

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\post;

use App\Models\ItemGroup;
use App\Models\WorkOrder;

describe('store', function (): void {
    it('rejects an invalid item group id', function (): void {
        $workOrder = WorkOrder::factory()->createOne();

        signIn(team: $workOrder->team);

        $response = post(route('teams.work-orders.lines.store', [
            'team' => $workOrder->team,
            'work_order' => $workOrder,
        ]), [
            'description' => 'Laptop computer',
            'item_group_id' => 'invalid',
            'quantity' => 2,
        ]);

        $response->assertRedirectBackWithErrors([
            'item_group_id' => 'The selected item group id is invalid.',
        ]);
    });

    it('rejects an item group from another tenant', function (): void {
        $workOrder = WorkOrder::factory()->createOne();
        $group = ItemGroup::factory()->createOne();

        signIn(team: $workOrder->team);

        $response = post(route('teams.work-orders.lines.store', [
            'team' => $workOrder->team,
            'work_order' => $workOrder,
        ]), [
            'description' => 'Laptop computer',
            'item_group_id' => $group->public_id,
            'quantity' => 2,
        ]);

        $response->assertRedirectBackWithErrors([
            'item_group_id' => 'The selected item group id is invalid.',
        ]);
    });

    it('rejects an inactive item group', function (): void {
        $workOrder = WorkOrder::factory()->createOne();
        $group = ItemGroup::factory()
            ->deactivated()
            ->for($workOrder->team)
            ->createOne();

        signIn(team: $workOrder->team);

        $response = post(route('teams.work-orders.lines.store', [
            'team' => $workOrder->team,
            'work_order' => $workOrder,
        ]), [
            'description' => 'Laptop computer',
            'item_group_id' => $group->public_id,
            'quantity' => 2,
        ]);

        $response->assertRedirectBackWithErrors([
            'item_group_id' => 'The selected item group id is invalid.',
        ]);
    });

    it('rejects a soft deleted item group', function (): void {
        $workOrder = WorkOrder::factory()->createOne();
        $group = ItemGroup::factory()
            ->trashed()
            ->for($workOrder->team)
            ->createOne();

        signIn(team: $workOrder->team);

        $response = post(route('teams.work-orders.lines.store', [
            'team' => $workOrder->team,
            'work_order' => $workOrder,
        ]), [
            'description' => 'Laptop computer',
            'item_group_id' => $group->public_id,
            'quantity' => 2,
        ]);

        $response->assertRedirectBackWithErrors([
            'item_group_id' => 'The selected item group id is invalid.',
        ]);
    });
});
```
