# Update Tests: Errors Final Record

Pest PATCH update: Mocked finality exceptions for principal and child update routes preserve response fields and supplied payload; these do not use final-state fixtures.

The action is mocked to throw. These assertions verify controller translation, not the underlying business guard.

## Maps a final record rejection to validation — variant 1

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\patch;

use App\Actions\WorkOrders\UpdateWorkOrder;
use App\Exceptions\WorkOrders\WorkOrderIsFinal;
use App\Models\WorkOrder;

describe('update', function (): void {
    it('maps a final record rejection to validation', function (): void {
        $workOrder = WorkOrder::factory()->createOne();

        signIn(team: $workOrder->team);

        mock(UpdateWorkOrder::class)
            ->shouldReceive('handle')
            ->once()
            ->andThrow(WorkOrderIsFinal::becauseItCannotBeChangedOrDeleted());

        $response = patch(route('teams.work-orders.update', [
            'team' => $workOrder->team,
            'work_order' => $workOrder,
        ]), ['note' => 'No']);

        $response->assertRedirectBackWithErrors([
            'work_order' => 'Work orders in a final status cannot be changed or deleted.',
        ]);
    });
});
```

## Maps a final record rejection to validation — variant 2

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\patch;

use App\Actions\WorkOrderLines\UpdateWorkOrderLine;
use App\Exceptions\WorkOrders\WorkOrderIsFinal;
use App\Models\WorkOrderLine;

describe('update', function (): void {
    it('maps a final record rejection to validation', function (): void {
        $line = WorkOrderLine::factory()->createOne();

        signIn(team: $line->workOrder->team);

        mock(UpdateWorkOrderLine::class)
            ->shouldReceive('handle')
            ->once()
            ->andThrow(WorkOrderIsFinal::becauseItCannotBeChangedOrDeleted());

        $response = patch(
            route('teams.work-orders.lines.update', [
                'team' => $line->workOrder->team,
                'work_order' => $line->workOrder,
                'line' => $line,
            ]),
            ['description' => 'Updated'],
        );

        $response->assertRedirectBackWithErrors([
            'work_order' =>
                'Work order lines cannot be updated after the work order reaches a final status.',
        ]);
    });
});
```
