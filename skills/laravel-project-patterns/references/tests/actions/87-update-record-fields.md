# Action Tests: Update Record Fields

Integration action tests: Update all supported fields and preserve assignment configuration; assert returned identity. A partial update preserves omitted owner, reference and carrier fields.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Actions\WorkOrders\Inputs\UpdateWorkOrderInput;
use App\Actions\WorkOrders\UpdateWorkOrder;
use App\Enums\LengthUnit;
use App\Enums\WeightUnit;
use App\Models\Cabinet;
use App\Models\Facility;
use App\Models\Member;
use App\Models\PlanRule;
use App\Models\ServicePlan;
use App\Models\Team;
use App\Models\WorkOrder;

it('updates a record without mutating the assignment', function (): void {
    $team = Team::factory()->createOne();
    $workOrder = WorkOrder::factory()->for($team)->createOne();
    $member = Member::factory()->recycle($team)->createOne();
    $servicePlan = ServicePlan::factory()->recycle($team)->createOne([
        'weight_unit' => WeightUnit::Pounds,
    ]);
    $planRule = PlanRule::factory()->recycle($servicePlan)->createOne();
    $cabinet = Cabinet::factory()->for($member)->recycle($team)->recycle($servicePlan)->createOne();
    $receivedFacility = Facility::factory()->recycle($team)->createOne();
    $pickupFacility = Facility::factory()->recycle($team)->createOne();
    $cabinetMemberId = $cabinet->member_id;
    $cabinetServicePlanId = $cabinet->service_plan_id;

    $updatedWorkOrder = resolve(UpdateWorkOrder::class)->handle(
        $workOrder,
        UpdateWorkOrderInput::from([
            'cabinet_id' => $cabinet->id,
            'dimension_unit' => LengthUnit::Inches->value,
            'external_carrier_name' => 'Example Carrier A',
            'external_tracking_number' => 'TRACK-999',
            'height' => '3.0000',
            'length' => '10.0000',
            'member_id' => $member->id,
            'note' => 'Updated note',
            'pickup_facility_id' => $pickupFacility->id,
            'plan_rule_id' => $planRule->id,
            'received_at' => '2026-07-31 10:30:00',
            'received_facility_id' => $receivedFacility->id,
            'received_label_text' => 'Updated label',
            'reference' => 'REC-UPDATED',
            'service_plan_id' => $servicePlan->id,
            'weight' => '2.5000',
            'weight_unit' => WeightUnit::Pounds->value,
            'width' => '6.0000',
        ]),
    );

    expect($updatedWorkOrder->is($workOrder))->toBeTrue();

    assertDatabaseHas(Cabinet::class, [
        'id' => $cabinet->id,
        'member_id' => $cabinetMemberId,
        'service_plan_id' => $cabinetServicePlanId,
    ]);

    assertDatabaseHas(WorkOrder::class, [
        'id' => $workOrder->id,
        'cabinet_id' => $cabinet->id,
        'member_id' => $member->id,
        'pickup_facility_id' => $pickupFacility->id,
        'plan_rule_id' => $planRule->id,
        'received_facility_id' => $receivedFacility->id,
        'service_plan_id' => $servicePlan->id,
        'dimension_unit' => LengthUnit::Inches->value,
        'external_carrier_name' => 'Example Carrier A',
        'external_tracking_number' => 'TRACK-999',
        'height' => '3.0000',
        'length' => '10.0000',
        'note' => 'Updated note',
        'received_at' => '2026-07-31 10:30:00',
        'received_label_text' => 'Updated label',
        'reference' => 'REC-UPDATED',
        'weight' => '2.5000',
        'weight_unit' => WeightUnit::Pounds->value,
        'width' => '6.0000',
    ]);
});

it('updates only provided fields', function (): void {
    $workOrder = WorkOrder::factory()->withMember()->createOne([
        'external_carrier_name' => 'Example Carrier B',
        'reference' => 'REC-ORIGINAL',
    ]);
    $memberId = $workOrder->member_id;

    resolve(UpdateWorkOrder::class)->handle($workOrder, UpdateWorkOrderInput::from([
        'note' => 'Only this changed',
    ]));

    assertDatabaseHas(WorkOrder::class, [
        'id' => $workOrder->id,
        'member_id' => $memberId,
        'external_carrier_name' => 'Example Carrier B',
        'note' => 'Only this changed',
        'reference' => 'REC-ORIGINAL',
    ]);
});
```
