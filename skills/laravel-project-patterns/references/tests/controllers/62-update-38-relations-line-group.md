# Update Tests: Relations Line Group

Pest PATCH update: A new foreign/inactive/deleted item group is rejected, but the explicitly unchanged inactive selected group is delegated successfully with the original typed input checks.

## Complete block

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\patch;

use App\Actions\WorkOrderLines\Inputs\UpdateWorkOrderLineInput;
use App\Actions\WorkOrderLines\UpdateWorkOrderLine;
use App\Models\ItemGroup;
use App\Models\WorkOrder;
use App\Models\WorkOrderLine;

describe('update', function (): void {
    it('rejects a newly assigned relation from another tenant', function (): void {
        $line = WorkOrderLine::factory()->createOne();
        $group = ItemGroup::factory()->createOne();

        signIn(team: $line->workOrder->team);

        $response = patch(route('teams.work-orders.lines.update', [
            'team' => $line->workOrder->team,
            'work_order' => $line->workOrder,
            'line' => $line,
        ]), [
            'item_group_id' => $group->public_id,
        ]);

        $response->assertRedirectBackWithErrors([
            'item_group_id' => 'The selected item group id is invalid.',
        ]);
    });

    it('rejects a newly assigned inactive relation', function (): void {
        $line = WorkOrderLine::factory()->createOne();
        $group = ItemGroup::factory()
            ->deactivated()
            ->for($line->workOrder->team)
            ->createOne();

        signIn(team: $line->workOrder->team);

        $response = patch(route('teams.work-orders.lines.update', [
            'team' => $line->workOrder->team,
            'work_order' => $line->workOrder,
            'line' => $line,
        ]), [
            'item_group_id' => $group->public_id,
        ]);

        $response->assertRedirectBackWithErrors([
            'item_group_id' => 'The selected item group id is invalid.',
        ]);
    });

    it('rejects a newly assigned soft deleted relation', function (): void {
        $line = WorkOrderLine::factory()->createOne();
        $group = ItemGroup::factory()
            ->trashed()
            ->for($line->workOrder->team)
            ->createOne();

        signIn(team: $line->workOrder->team);

        $response = patch(route('teams.work-orders.lines.update', [
            'team' => $line->workOrder->team,
            'work_order' => $line->workOrder,
            'line' => $line,
        ]), [
            'item_group_id' => $group->public_id,
        ]);

        $response->assertRedirectBackWithErrors([
            'item_group_id' => 'The selected item group id is invalid.',
        ]);
    });

    it('accepts a current inactive relation', function (): void {
        $group = ItemGroup::factory()->deactivated()->createOne();
        $workOrder = WorkOrder::factory()->for($group->team)->createOne();
        $line = WorkOrderLine::factory()
            ->for($workOrder)
            ->for($group, 'itemGroup')
            ->createOne();

        signIn(team: $workOrder->team);

        mock(UpdateWorkOrderLine::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(
                fn (WorkOrderLine $lineArgument, UpdateWorkOrderLineInput $input): bool => $lineArgument
                    ->is($line)
                    && $input->itemGroupId === $group->id,
            );

        $response = patch(route('teams.work-orders.lines.update', [
            'team' => $workOrder->team,
            'work_order' => $workOrder,
            'line' => $line,
        ]), [
            'item_group_id' => $group->public_id,
        ]);

        $response->assertRedirectToRoute('teams.work-orders.lines.show', [
            'team' => $workOrder->team,
            'work_order' => $workOrder,
            'line' => $line,
        ]);
    });
});
```
