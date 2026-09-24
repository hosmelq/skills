# Update Tests: Mapping Record Partial Input

Pest PATCH update: A partial record request maps the submitted note and asserts reference is Optional. This example does not check every omitted DTO property.

## Maps submitted and omitted fields to the action

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\patch;

use App\Actions\WorkOrders\Inputs\UpdateWorkOrderInput;
use App\Actions\WorkOrders\UpdateWorkOrder;
use App\Models\WorkOrder;
use Spatie\LaravelData\Optional;

describe('update', function (): void {
    it('maps submitted and omitted fields to the action', function (): void {
        $workOrder = WorkOrder::factory()->createOne();

        login(team: $workOrder->team);

        mock(UpdateWorkOrder::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(
                fn (WorkOrder $workOrderArgument, UpdateWorkOrderInput $input): bool => $workOrderArgument
                    ->is($workOrder)
                    && $input->note === 'Updated note'
                    && $input->reference instanceof Optional,
            );

        $response = patch(route('teams.work-orders.update', [
            'team' => $workOrder->team,
            'work_order' => $workOrder,
        ]), ['note' => 'Updated note']);

        $response->assertRedirectToRoute('teams.work-orders.show', [
            'team' => $workOrder->team,
            'work_order' => $workOrder,
        ])
            ->assertToast('Work order updated');
    });
});
```
