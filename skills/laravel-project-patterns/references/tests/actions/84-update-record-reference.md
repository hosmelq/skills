# Action Tests: Update Reference Uniqueness

Integration action tests: Reject a case-insensitive collision with another active record, accept the current reference with changed case, and reuse a soft-deleted record reference.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Actions\WorkOrders\Inputs\UpdateWorkOrderInput;
use App\Actions\WorkOrders\UpdateWorkOrder;
use App\Exceptions\WorkOrders\WorkOrderReferenceAlreadyExists;
use App\Models\Team;
use App\Models\WorkOrder;

it('rejects a case-insensitive active reference collision', function (): void {
    $team = Team::factory()->createOne();
    $workOrder = WorkOrder::factory()->for($team)->createOne();
    WorkOrder::factory()->for($team)->createOne(['reference' => 'REC-TAKEN']);

    expect(fn () => resolve(UpdateWorkOrder::class)->handle($workOrder, UpdateWorkOrderInput::from([
        'reference' => 'rec-taken',
    ])))->toThrow(
        WorkOrderReferenceAlreadyExists::class,
        'The work order reference already exists.',
    );
});

it('accepts its own reference regardless of case', function (): void {
    $workOrder = WorkOrder::factory()->createOne(['reference' => 'REC-SELF']);

    resolve(UpdateWorkOrder::class)->handle(
        $workOrder,
        UpdateWorkOrderInput::from(['reference' => 'rec-self']),
    );

    assertDatabaseHas(WorkOrder::class, [
        'id' => $workOrder->id,
        'reference' => 'rec-self',
    ]);
});

it('reuses a reference from a soft-deleted record', function (): void {
    $team = Team::factory()->createOne();
    $workOrder = WorkOrder::factory()->for($team)->createOne();
    WorkOrder::factory()->trashed()->for($team)->createOne(['reference' => 'REC-TAKEN']);

    resolve(UpdateWorkOrder::class)->handle(
        $workOrder,
        UpdateWorkOrderInput::from(['reference' => 'rec-taken']),
    );

    assertDatabaseHas(WorkOrder::class, [
        'id' => $workOrder->id,
        'reference' => 'rec-taken',
    ]);
});
```
