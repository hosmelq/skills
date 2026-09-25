# Action Tests: Create Required Fields

Integration action tests: Create with empty optional input and assert the full default/null vector; accept weight before a plan is selected.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Actions\WorkOrders\CreateWorkOrder;
use App\Actions\WorkOrders\Inputs\CreateWorkOrderInput;
use App\Enums\WeightUnit;
use App\Models\Team;
use App\Models\WorkOrder;
use App\Models\WorkOrderStatus;

it('creates a record with only required fields', function (): void {
    $team = Team::factory()->createOne();
    $status = WorkOrderStatus::factory()->initial()->recycle($team)->createOne();

    $workOrder = resolve(CreateWorkOrder::class)->handle($team, CreateWorkOrderInput::from([]));

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

it('accepts weight before a plan is selected', function (): void {
    $team = Team::factory()->createOne();
    WorkOrderStatus::factory()->initial()->recycle($team)->createOne();

    $workOrder = resolve(CreateWorkOrder::class)->handle($team, CreateWorkOrderInput::from([
        'weight' => '1.0000',
        'weight_unit' => WeightUnit::Kilograms->value,
    ]));

    assertDatabaseHas(WorkOrder::class, [
        'id' => $workOrder->id,
        'service_plan_id' => null,
        'weight' => '1.0000',
        'weight_unit' => WeightUnit::Kilograms->value,
    ]);
});
```
