# Action Tests: Create Record Fields

Integration action tests: Persist the complete create input: owner, assignment, distinct relation roles, plan and rule, dates, reference, notes and typed measurements; assert returned model and every field.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Actions\WorkOrders\CreateWorkOrder;
use App\Actions\WorkOrders\Inputs\CreateWorkOrderInput;
use App\Enums\LengthUnit;
use App\Enums\WeightUnit;
use App\Models\Cabinet;
use App\Models\Facility;
use App\Models\Member;
use App\Models\PlanRule;
use App\Models\ServicePlan;
use App\Models\Team;
use App\Models\WorkOrder;
use App\Models\WorkOrderStatus;

it('creates a record', function (): void {
    $team = Team::factory()->createOne();
    $status = WorkOrderStatus::factory()->initial()->recycle($team)->createOne();
    $receivedFacility = Facility::factory()->recycle($team)->createOne();
    $currentFacility = Facility::factory()->recycle($team)->createOne();
    $pickupFacility = Facility::factory()->recycle($team)->createOne();
    $member = Member::factory()->recycle($team)->createOne();
    $servicePlan = ServicePlan::factory()->recycle($team)->createOne([
        'weight_unit' => WeightUnit::Pounds,
    ]);
    $planRule = PlanRule::factory()->recycle($servicePlan)->createOne();
    $cabinet = Cabinet::factory()->for($member)->recycle($team)->recycle($servicePlan)->createOne();
    $workOrder = resolve(CreateWorkOrder::class)->handle($team, CreateWorkOrderInput::from([
        'cabinet_id' => $cabinet->id,
        'current_facility_id' => $currentFacility->id,
        'dimension_unit' => LengthUnit::Inches->value,
        'external_carrier_name' => 'External carrier',
        'external_tracking_number' => 'TRACK-123',
        'height' => '3.2500',
        'length' => '10.5000',
        'member_id' => $member->id,
        'note' => 'Handle with care.',
        'pickup_facility_id' => $pickupFacility->id,
        'plan_rule_id' => $planRule->id,
        'received_at' => '2026-07-31 10:30:00',
        'received_facility_id' => $receivedFacility->id,
        'received_label_text' => 'Original label',
        'reference' => 'REC-1001',
        'service_plan_id' => $servicePlan->id,
        'weight' => '2.5000',
        'weight_unit' => WeightUnit::Pounds->value,
        'width' => '6.7500',
        'work_order_status_id' => $status->id,
    ]));

    expect($workOrder)->toBeInstanceOf(WorkOrder::class);

    assertDatabaseHas(WorkOrder::class, [
        'id' => $workOrder->id,
        'cabinet_id' => $cabinet->id,
        'current_facility_id' => $currentFacility->id,
        'member_id' => $member->id,
        'pickup_facility_id' => $pickupFacility->id,
        'plan_rule_id' => $planRule->id,
        'received_facility_id' => $receivedFacility->id,
        'service_plan_id' => $servicePlan->id,
        'team_id' => $team->id,
        'work_order_status_id' => $status->id,
        'dimension_unit' => LengthUnit::Inches->value,
        'external_carrier_name' => 'External carrier',
        'external_tracking_number' => 'TRACK-123',
        'height' => '3.2500',
        'length' => '10.5000',
        'note' => 'Handle with care.',
        'received_at' => '2026-07-31 10:30:00',
        'received_label_text' => 'Original label',
        'reference' => 'REC-1001',
        'weight' => '2.5000',
        'weight_unit' => WeightUnit::Pounds->value,
        'width' => '6.7500',
    ]);
});
```
