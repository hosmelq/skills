# Edit Tests: Current Inactive Option Alongside Active Choices

GET edit retains the selected inactive option in the target payload and at options[0], with public ID/name and a nonnull inactive timestamp; options[1] contains an active choice. Another inactive choice is seeded separately. Preserve current selection without treating every inactive record as eligible.

The example asserts those positions, not an exact list count or exhaustive exclusion of every other option.

## Selected Option

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\ItemGroup;
use App\Models\WorkOrder;
use App\Models\WorkOrderLine;
use Inertia\Testing\AssertableInertia;

describe('edit', function (): void {
    it('shows the edit page with the current inactive option', function (): void {
        $currentGroup = ItemGroup::factory()->deactivated()->createOne();
        $workOrder = WorkOrder::factory()->recycle($currentGroup->team)->createOne();
        $line = WorkOrderLine::factory()
            ->recycle($workOrder)
            ->for($currentGroup, 'itemGroup')
            ->createOne();
        $activeGroup = ItemGroup::factory()->recycle($workOrder->team)->createOne();
        ItemGroup::factory()->deactivated()->recycle($workOrder->team)->createOne();

        login(team: $workOrder->team);

        $response = get(route('teams.work-orders.lines.edit', [
            'team' => $workOrder->team,
            'work_order' => $workOrder,
            'line' => $line,
        ]));

        $response->assertOk()
            ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
                ->component('work-orders/lines/Edit')
                ->where('line.id', $line->sqid)
                ->where('line.group.id', $currentGroup->sqid)
                ->where('itemGroups.0.id', $currentGroup->sqid)
                ->where('itemGroups.0.name', $currentGroup->name)
                ->where(
                    'itemGroups.0.deactivated_at',
                    fn (mixed $value): bool => $value !== null,
                )
                ->where('itemGroups.1.id', $activeGroup->sqid)
                ->where('itemGroups.1.name', $activeGroup->name));
    });
});
```
