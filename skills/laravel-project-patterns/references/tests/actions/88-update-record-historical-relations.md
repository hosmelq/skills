# Action Tests: Preserve Historical Relation Selections

Integration action tests: Retain the current trashed owner but reject newly assigning it elsewhere; preserve explicitly unchanged inactive or trashed assignment, role-specific relations, plan and rule.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Actions\WorkOrders\Inputs\UpdateWorkOrderInput;
use App\Actions\WorkOrders\UpdateWorkOrder;
use App\Exceptions\WorkOrders\MemberIsUnavailable;
use App\Models\Cabinet;
use App\Models\Facility;
use App\Models\Member;
use App\Models\PlanRule;
use App\Models\ServicePlan;
use App\Models\Team;
use App\Models\WorkOrder;

it('preserves the current historical owner but rejects reassignment', function (): void {
    $team = Team::factory()->createOne();
    $workOrder = WorkOrder::factory()
        ->withMember(Member::factory()->trashed())
        ->for($team)
        ->createOne();

    resolve(UpdateWorkOrder::class)->handle($workOrder, UpdateWorkOrderInput::from([
        'member_id' => $workOrder->member->id,
    ]));

    assertDatabaseHas(WorkOrder::class, [
        'id' => $workOrder->id,
        'member_id' => $workOrder->member->id,
    ]);

    $otherWorkOrder = WorkOrder::factory()->for($team)->createOne();

    expect(fn () => resolve(UpdateWorkOrder::class)->handle(
        $otherWorkOrder,
        UpdateWorkOrderInput::from([
            'member_id' => $workOrder->member->id,
        ]),
    ))->toThrow(
        MemberIsUnavailable::class,
        'The selected member is unavailable.',
    );
});

it('preserves explicitly unchanged historical relations', function (): void {
    $team = Team::factory()->createOne();
    $workOrder = WorkOrder::factory()
        ->withPickupFacility(Facility::factory()->deactivated())
        ->withReceivedFacility(Facility::factory()->trashed())
        ->withServicePlan(ServicePlan::factory()->deactivated()->trashed())
        ->withCabinet(Cabinet::factory()->deactivated()->trashed())
        ->withPlanRule(PlanRule::factory()->trashed())
        ->for($team)
        ->createOne();

    resolve(UpdateWorkOrder::class)->handle($workOrder, UpdateWorkOrderInput::from([
        'cabinet_id' => $workOrder->cabinet->id,
        'pickup_facility_id' => $workOrder->pickupFacility->id,
        'plan_rule_id' => $workOrder->planRule->id,
        'received_facility_id' => $workOrder->receivedFacility->id,
        'service_plan_id' => $workOrder->servicePlan->id,
    ]));

    assertDatabaseHas(WorkOrder::class, [
        'id' => $workOrder->id,
        'cabinet_id' => $workOrder->cabinet->id,
        'pickup_facility_id' => $workOrder->pickupFacility->id,
        'plan_rule_id' => $workOrder->planRule->id,
        'received_facility_id' => $workOrder->receivedFacility->id,
        'service_plan_id' => $workOrder->servicePlan->id,
    ]);
});
```
