# Action Tests: Create Defaults and Explicit Nulls

Integration action tests: Default the initial state, timestamp and current relation from received; do not reverse-infer received from current. Explicit nulls retain documented defaults and nullable fields.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Actions\WorkOrders\CreateWorkOrder;
use App\Actions\WorkOrders\Inputs\CreateWorkOrderInput;
use App\Models\Facility;
use App\Models\Team;
use App\Models\WorkOrder;
use App\Models\WorkOrderStatus;

it('applies initial state timestamp and current relation defaults', function (): void {
    $team = Team::factory()->createOne();
    $status = WorkOrderStatus::factory()->initial()->recycle($team)->createOne();
    $receivedFacility = Facility::factory()->recycle($team)->createOne();

    $workOrder = resolve(CreateWorkOrder::class)->handle($team, CreateWorkOrderInput::from([
        'received_facility_id' => $receivedFacility->id,
    ]));

    assertDatabaseHas(WorkOrder::class, [
        'id' => $workOrder->id,
        'current_facility_id' => $receivedFacility->id,
        'received_facility_id' => $receivedFacility->id,
        'work_order_status_id' => $status->id,
        'received_at' => now(),
    ]);
});

it('does not infer the received relation from the current relation', function (): void {
    $team = Team::factory()->createOne();
    WorkOrderStatus::factory()->initial()->recycle($team)->createOne();
    $currentFacility = Facility::factory()->recycle($team)->createOne();

    $workOrder = resolve(CreateWorkOrder::class)->handle($team, CreateWorkOrderInput::from([
        'current_facility_id' => $currentFacility->id,
    ]));

    assertDatabaseHas(WorkOrder::class, [
        'id' => $workOrder->id,
        'current_facility_id' => $currentFacility->id,
        'received_facility_id' => null,
    ]);
});

it('accepts explicit nulls and applies defaults', function (): void {
    $team = Team::factory()->createOne();
    $status = WorkOrderStatus::factory()->initial()->recycle($team)->createOne();

    $workOrder = resolve(CreateWorkOrder::class)->handle($team, CreateWorkOrderInput::from([
        'cabinet_id' => null,
        'current_facility_id' => null,
        'dimension_unit' => null,
        'external_carrier_name' => null,
        'external_tracking_number' => null,
        'height' => null,
        'length' => null,
        'member_id' => null,
        'note' => null,
        'pickup_facility_id' => null,
        'plan_rule_id' => null,
        'received_at' => null,
        'received_facility_id' => null,
        'received_label_text' => null,
        'reference' => null,
        'service_plan_id' => null,
        'weight' => null,
        'weight_unit' => null,
        'width' => null,
        'work_order_status_id' => null,
    ]));

    assertDatabaseHas(WorkOrder::class, [
        'id' => $workOrder->id,
        'cabinet_id' => null,
        'current_facility_id' => null,
        'member_id' => null,
        'pickup_facility_id' => null,
        'plan_rule_id' => null,
        'received_facility_id' => null,
        'service_plan_id' => null,
        'work_order_status_id' => $status->id,
        'dimension_unit' => null,
        'external_carrier_name' => null,
        'external_tracking_number' => null,
        'height' => null,
        'length' => null,
        'note' => null,
        'received_at' => now(),
        'received_label_text' => null,
        'reference' => null,
        'weight' => null,
        'weight_unit' => null,
        'width' => null,
    ]);
});
```
