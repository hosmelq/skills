# Update Tests: Errors Reference And Date

Pest PATCH update: Mocked required-date and duplicate-reference failures retain their separate exact validation keys and responses.

The action is mocked to throw. These assertions verify controller translation, not the underlying business guard.

## Complete block

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\patch;

use App\Actions\WorkOrders\UpdateWorkOrder;
use App\Exceptions\WorkOrders\WorkOrderReferenceAlreadyExists;
use App\Exceptions\WorkOrders\WorkOrderRequiresReceivedAt;
use App\Models\WorkOrder;

describe('update', function (): void {
    it('maps a required received date rejection to validation', function (): void {
        $workOrder = WorkOrder::factory()->createOne();

        signIn(team: $workOrder->team);

        mock(UpdateWorkOrder::class)
            ->shouldReceive('handle')
            ->once()
            ->andThrow(WorkOrderRequiresReceivedAt::becauseItIsRequired());

        $response = patch(route('teams.work-orders.update', [
            'team' => $workOrder->team,
            'work_order' => $workOrder,
        ]), ['note' => 'No']);

        $response->assertRedirectBackWithErrors([
            'received_at' => 'The received date is required.',
        ]);
    });

    it('maps a duplicate reference rejection to validation', function (): void {
        $workOrder = WorkOrder::factory()->createOne();

        signIn(team: $workOrder->team);

        mock(UpdateWorkOrder::class)
            ->shouldReceive('handle')
            ->once()
            ->andThrow(WorkOrderReferenceAlreadyExists::becauseItIsAlreadyInUse());

        $response = patch(route('teams.work-orders.update', [
            'team' => $workOrder->team,
            'work_order' => $workOrder,
        ]), ['note' => 'No']);

        $response->assertRedirectBackWithErrors([
            'reference' => 'The reference has already been taken.',
        ]);
    });
});
```
