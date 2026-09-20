# Edit Tests: Historical Selections And Available Options

GET edit retains eight selected soft-deleted relations by public ID, including a nonfinal status, while exposing a separate active member option and available-list props. It checks full unit enums and the absence of unsupported status/group options; selected resources are distinct from new choices.

The example checks one active member, the presence of four lists and two absent props; it does not assert complete contents or exclusion rules for every list. Factory helpers preserve one consistent tenant across the historical graph.

## Historical Selections

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

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
use Inertia\Testing\AssertableInertia;

describe('edit', function (): void {
    it('shows the edit page with its selected historical relations', function (): void {
        $team = Team::factory()->createOne();
        $status = WorkOrderStatus::factory()->trashed()->for($team)->createOne();
        $workOrder = WorkOrder::factory()
            ->withCurrentFacility(Facility::factory()->trashed())
            ->withMember(Member::factory()->trashed())
            ->withPickupFacility(Facility::factory()->trashed())
            ->withReceivedFacility(Facility::factory()->trashed())
            ->withServicePlan(ServicePlan::factory()->trashed())
            ->withCabinet(Cabinet::factory()->trashed())
            ->withPlanRule(PlanRule::factory()->trashed())
            ->for($status, 'workOrderStatus')
            ->for($team)
            ->createOne();
        $activeMember = Member::factory()->for($team)->createOne();

        signIn(team: $team);

        $response = get(route('teams.work-orders.edit', [
            'team' => $team,
            'work_order' => $workOrder,
        ]));

        $response->assertOk()
            ->assertInertia(function (AssertableInertia $page) use (
                $activeMember,
                $team,
                $workOrder,
                $status,
            ): void {
                $page->component('work-orders/Edit')
                    ->where('team.id', $team->public_id)
                    ->where('workOrder.id', $workOrder->public_id)
                    ->where('workOrder.current_facility.id', $workOrder->currentFacility->public_id)
                    ->where('workOrder.member.id', $workOrder->member->public_id)
                    ->where('workOrder.cabinet.id', $workOrder->cabinet->public_id)
                    ->where('workOrder.status.id', $status->public_id)
                    ->where('workOrder.status.is_final', false)
                    ->where('workOrder.pickup_facility.id', $workOrder->pickupFacility->public_id)
                    ->where('workOrder.received_facility.id', $workOrder->receivedFacility->public_id)
                    ->where('workOrder.service_plan.id', $workOrder->servicePlan->public_id)
                    ->where(
                        'workOrder.plan_rule.id',
                        $workOrder->planRule->public_id,
                    )
                    ->where('members.0.id', $activeMember->public_id)
                    ->where('members.0.display_name', $activeMember->display_name)
                    ->has('facilities')
                    ->has('cabinets')
                    ->has('planRules')
                    ->has('servicePlans')
                    ->where('lengthUnits', LengthUnit::options())
                    ->where('weightUnits', WeightUnit::options())
                    ->missing('statuses')
                    ->missing('itemGroups');
            });
    });
});
```
