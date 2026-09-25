# Action Tests: Clear Nullable Record Fields

Integration action tests: Explicit null clears the owner, assignment, pickup relation, plan, note and reference.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Actions\WorkOrders\Inputs\UpdateWorkOrderInput;
use App\Actions\WorkOrders\UpdateWorkOrder;
use App\Models\WorkOrder;

it('clears nullable fields', function (): void {
    $workOrder = WorkOrder::factory()->withCabinet()->withPickupFacility()->createOne([
        'note' => 'Clear me',
        'reference' => 'CLEAR-ME',
    ]);

    resolve(UpdateWorkOrder::class)->handle($workOrder, UpdateWorkOrderInput::from([
        'cabinet_id' => null,
        'member_id' => null,
        'note' => null,
        'pickup_facility_id' => null,
        'reference' => null,
        'service_plan_id' => null,
    ]));

    assertDatabaseHas(WorkOrder::class, [
        'id' => $workOrder->id,
        'cabinet_id' => null,
        'member_id' => null,
        'pickup_facility_id' => null,
        'service_plan_id' => null,
        'note' => null,
        'reference' => null,
    ]);
});
```
