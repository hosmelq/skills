# Show Tests: All Selected Historical Relations

A live GET show record retains eight explicitly selected soft deleted relations. The complete fixture graph and assertions cover every related public ID; historical retention does not imply eligibility for new selections.

## Historical Detail

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\Cabinet;
use App\Models\Facility;
use App\Models\Member;
use App\Models\PlanRule;
use App\Models\ServicePlan;
use App\Models\Team;
use App\Models\WorkOrder;
use App\Models\WorkOrderStatus;
use Inertia\Testing\AssertableInertia;

describe('show', function (): void {
    it('shows the detail page with its selected historical relations', function (): void {
        $team = Team::factory()->createOne();
        $status = WorkOrderStatus::factory()->trashed()->recycle($team)->createOne();
        $workOrder = WorkOrder::factory()
            ->withCurrentFacility(Facility::factory()->trashed())
            ->withMember(Member::factory()->trashed())
            ->withPickupFacility(Facility::factory()->trashed())
            ->withReceivedFacility(Facility::factory()->trashed())
            ->withServicePlan(ServicePlan::factory()->trashed())
            ->withCabinet(Cabinet::factory()->trashed())
            ->withPlanRule(PlanRule::factory()->trashed())
            ->for($status, 'workOrderStatus')
            ->recycle($team)
            ->createOne();

        login(team: $team);

        $response = get(route('teams.work-orders.show', [
            'team' => $team,
            'work_order' => $workOrder,
        ]));

        $response->assertOk()
            ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
                ->component('work-orders/Show')
                ->where('workOrder.current_facility.id', $workOrder->currentFacility->public_id)
                ->where('workOrder.member.id', $workOrder->member->public_id)
                ->where('workOrder.cabinet.id', $workOrder->cabinet->public_id)
                ->where('workOrder.status.id', $status->public_id)
                ->where('workOrder.pickup_facility.id', $workOrder->pickupFacility->public_id)
                ->where('workOrder.received_facility.id', $workOrder->receivedFacility->public_id)
                ->where('workOrder.service_plan.id', $workOrder->servicePlan->public_id)
                ->where(
                    'workOrder.plan_rule.id',
                    $workOrder->planRule->public_id,
                ));
    });
});
```
