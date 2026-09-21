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
    it('accepts the current historical cabinet, facilities and service plan', function (): void {
        $team = Team::factory()->createOne();
        $workOrder = WorkOrder::factory()
            ->withPickupFacility(Facility::factory()->deactivated())
            ->withReceivedFacility(Facility::factory()->trashed())
            ->withServicePlan(ServicePlan::factory()->deactivated()->trashed())
            ->withCabinet(Cabinet::factory()->deactivated()->trashed())
            ->for($team)
            ->createOne();

        signIn(team: $team);

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
            'cabinet_id' => $workOrder->cabinet->public_id,
            'pickup_facility_id' => $workOrder->pickupFacility->public_id,
            'received_facility_id' => $workOrder->receivedFacility->public_id,
            'service_plan_id' => $workOrder->servicePlan->public_id,
        ]);

        $response->assertRedirectToRoute('teams.work-orders.show', [
            'team' => $team,
            'work_order' => $workOrder,
        ])->assertToast('Work order updated');
    });

    it('accepts the current historical member', function (): void {
        $team = Team::factory()->createOne();
        $workOrder = WorkOrder::factory()
            ->withMember(Member::factory()->trashed())
            ->for($team)
            ->createOne();

        signIn(team: $workOrder->team);

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
        ]), ['member_id' => $workOrder->member->public_id]);

        $response->assertRedirectToRoute('teams.work-orders.show', [
            'team' => $workOrder->team,
            'work_order' => $workOrder,
        ])->assertToast('Work order updated');
    });

    it('accepts the current historical rule', function (): void {
        $team = Team::factory()->createOne();
        $workOrder = WorkOrder::factory()
            ->withServicePlan(ServicePlan::factory()->trashed())
            ->withPlanRule(PlanRule::factory()->trashed())
            ->for($team)
            ->createOne();

        signIn(team: $team);

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
            'plan_rule_id' => $workOrder->planRule->public_id,
        ]);

        $response->assertRedirectToRoute('teams.work-orders.show', [
            'team' => $team,
            'work_order' => $workOrder,
        ])->assertToast('Work order updated');
    });
});
```
