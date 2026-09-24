# Update Tests: Relations Current History

Pest PATCH update: Retain the current historical cabinet/facilities/plan, member and rule in three complete action-mapping fixtures. This does not establish eligibility for new selections.

## Complete block

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\patch;

use App\Actions\WorkOrders\Inputs\UpdateWorkOrderInput;
use App\Actions\WorkOrders\UpdateWorkOrder;
use App\Models\Cabinet;
use App\Models\Facility;
use App\Models\Member;
use App\Models\PlanRule;
use App\Models\ServicePlan;
use App\Models\Team;
use App\Models\WorkOrder;

describe('update', function (): void {
    it('accepts current historical relations', function (): void {
        $team = Team::factory()->createOne();
        $workOrder = WorkOrder::factory()
            ->withPickupFacility(Facility::factory()->deactivated())
            ->withReceivedFacility(Facility::factory()->trashed())
            ->withServicePlan(ServicePlan::factory()->deactivated()->trashed())
            ->withCabinet(Cabinet::factory()->deactivated()->trashed())
            ->recycle($team)
            ->createOne();

        login(team: $team);

        mock(UpdateWorkOrder::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(
                fn (WorkOrder $workOrderArgument, UpdateWorkOrderInput $input): bool => $workOrderArgument
                    ->is($workOrder)
                    && $input->cabinetId === $workOrder->cabinet->id
                    && $input->pickupFacilityId === $workOrder->pickupFacility->id
                    && $input->receivedFacilityId === $workOrder->receivedFacility->id
                    && $input->servicePlanId === $workOrder->servicePlan->id,
            );

        $response = patch(route('teams.work-orders.update', [
            'team' => $team,
            'work_order' => $workOrder,
        ]), [
            'cabinet_id' => $workOrder->cabinet->sqid,
            'pickup_facility_id' => $workOrder->pickupFacility->sqid,
            'received_facility_id' => $workOrder->receivedFacility->sqid,
            'service_plan_id' => $workOrder->servicePlan->sqid,
        ]);

        $response->assertRedirectToRoute('teams.work-orders.show', [
            'team' => $team,
            'work_order' => $workOrder,
        ])->assertToast('Work order updated');
    });

    it('accepts a current historical relation', function (): void {
        $team = Team::factory()->createOne();
        $workOrder = WorkOrder::factory()
            ->withMember(Member::factory()->trashed())
            ->recycle($team)
            ->createOne();

        login(team: $workOrder->team);

        mock(UpdateWorkOrder::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(
                fn (WorkOrder $workOrderArgument, UpdateWorkOrderInput $input): bool => $workOrderArgument
                    ->is($workOrder)
                    && $input->memberId === $workOrder->member->id,
            );

        $response = patch(route('teams.work-orders.update', [
            'team' => $workOrder->team,
            'work_order' => $workOrder,
        ]), ['member_id' => $workOrder->member->sqid]);

        $response->assertRedirectToRoute('teams.work-orders.show', [
            'team' => $workOrder->team,
            'work_order' => $workOrder,
        ])->assertToast('Work order updated');
    });

    it('accepts a current historical dependent relation', function (): void {
        $team = Team::factory()->createOne();
        $workOrder = WorkOrder::factory()
            ->withServicePlan(ServicePlan::factory()->trashed())
            ->withPlanRule(PlanRule::factory()->trashed())
            ->recycle($team)
            ->createOne();

        login(team: $team);

        mock(UpdateWorkOrder::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(
                fn (WorkOrder $workOrderArgument, UpdateWorkOrderInput $input): bool => $workOrderArgument
                    ->is($workOrder)
                    && $input->planRuleId
                        === $workOrder->planRule->id,
            );

        $response = patch(route('teams.work-orders.update', [
            'team' => $team,
            'work_order' => $workOrder,
        ]), [
            'plan_rule_id' => $workOrder->planRule->sqid,
        ]);

        $response->assertRedirectToRoute('teams.work-orders.show', [
            'team' => $team,
            'work_order' => $workOrder,
        ])->assertToast('Work order updated');
    });
});
```
