# Update Tests: Errors Unavailable Group

Pest PATCH update: The child update action reports a now-unavailable selected item group; retain the constructed group, payload and exception-to-field mapping.

The action is mocked to throw. These assertions verify controller translation, not the underlying business guard.

## Maps an unavailable relation rejection to validation

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\patch;

use App\Actions\WorkOrderLines\UpdateWorkOrderLine;
use App\Exceptions\WorkOrderLines\ItemGroupIsUnavailable;
use App\Models\ItemGroup;
use App\Models\WorkOrderLine;

describe('update', function (): void {
    it('maps an unavailable relation rejection to validation', function (): void {
        $line = WorkOrderLine::factory()->createOne();
        $group = ItemGroup::factory()->recycle($line->workOrder->team)->createOne();

        login(team: $line->workOrder->team);

        mock(UpdateWorkOrderLine::class)
            ->shouldReceive('handle')
            ->once()
            ->andThrow(ItemGroupIsUnavailable::becauseItIsUnavailable());

        $response = patch(route('teams.work-orders.lines.update', [
            'team' => $line->workOrder->team,
            'work_order' => $line->workOrder,
            'line' => $line,
        ]), [
            'item_group_id' => $group->public_id,
        ]);

        $response->assertRedirectBackWithErrors([
            'item_group_id' =>
                'The selected item group is unavailable.',
        ]);
    });
});
```
