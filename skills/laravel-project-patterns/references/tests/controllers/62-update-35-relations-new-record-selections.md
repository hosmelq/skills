# Update Tests: Relations New Record Selections

Pest PATCH update: Newly assigned principal-record relations from another tenant, inactive relations and soft-deleted relations remain separate complete fixtures and field-error sets.

## Complete block

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\patch;

use App\Actions\WorkOrders\UpdateWorkOrder;
use App\Models\Cabinet;
use App\Models\Facility;
use App\Models\Member;
use App\Models\PlanRule;
use App\Models\ServicePlan;
use App\Models\Team;
use App\Models\WorkOrder;

describe('update', function (): void {
    it('rejects a newly assigned relation from another tenant', function (): void {
        $workOrder = WorkOrder::factory()->createOne();
        $member = Member::factory()->createOne();
        $facility = Facility::factory()->createOne();
        $cabinet = Cabinet::factory()->createOne();
        $servicePlan = ServicePlan::factory()->createOne();

        login(team: $workOrder->team);

        mock(UpdateWorkOrder::class)
            ->shouldNotReceive('handle');

        $response = patch(route('teams.work-orders.update', [
            'team' => $workOrder->team,
            'work_order' => $workOrder,
        ]), [
            'member_id' => $member->sqid,
            'cabinet_id' => $cabinet->sqid,
            'pickup_facility_id' => $facility->sqid,
            'received_facility_id' => $facility->sqid,
            'service_plan_id' => $servicePlan->sqid,
        ]);

        $response->assertRedirectBackWithErrors([
            'member_id' => 'The selected member id is invalid.',
            'cabinet_id' => 'The selected cabinet id is invalid.',
            'pickup_facility_id' => 'The selected pickup facility id is invalid.',
            'received_facility_id' => 'The selected received facility id is invalid.',
            'service_plan_id' => 'The selected service plan id is invalid.',
        ]);
    });

    it('rejects a newly assigned inactive relation', function (): void {
        $team = Team::factory()->createOne();
        $workOrder = WorkOrder::factory()->recycle($team)->createOne();
        $facility = Facility::factory()->deactivated()->recycle($team)->createOne();
        $cabinet = Cabinet::factory()->deactivated()->recycle($team)->createOne();
        $servicePlan = ServicePlan::factory()->deactivated()->recycle($team)->createOne();

        login(team: $team);

        mock(UpdateWorkOrder::class)
            ->shouldNotReceive('handle');

        $response = patch(route('teams.work-orders.update', [
            'team' => $team,
            'work_order' => $workOrder,
        ]), [
            'cabinet_id' => $cabinet->sqid,
            'pickup_facility_id' => $facility->sqid,
            'received_facility_id' => $facility->sqid,
            'service_plan_id' => $servicePlan->sqid,
        ]);

        $response->assertRedirectBackWithErrors([
            'cabinet_id' => 'The selected cabinet id is invalid.',
            'pickup_facility_id' => 'The selected pickup facility id is invalid.',
            'received_facility_id' => 'The selected received facility id is invalid.',
            'service_plan_id' => 'The selected service plan id is invalid.',
        ]);
    });

    it('rejects a newly assigned soft deleted relation', function (): void {
        $team = Team::factory()->createOne();
        $workOrder = WorkOrder::factory()->recycle($team)->createOne();
        $member = Member::factory()->trashed()->recycle($team)->createOne();
        $facility = Facility::factory()->trashed()->recycle($team)->createOne();
        $cabinet = Cabinet::factory()->trashed()->recycle($team)->createOne();
        $servicePlan = ServicePlan::factory()->trashed()->recycle($team)->createOne();
        $planRule = PlanRule::factory()->trashed()->recycle($team)->createOne();

        login(team: $team);

        mock(UpdateWorkOrder::class)
            ->shouldNotReceive('handle');

        $response = patch(route('teams.work-orders.update', [
            'team' => $team,
            'work_order' => $workOrder,
        ]), [
            'member_id' => $member->sqid,
            'cabinet_id' => $cabinet->sqid,
            'pickup_facility_id' => $facility->sqid,
            'received_facility_id' => $facility->sqid,
            'service_plan_id' => $servicePlan->sqid,
            'plan_rule_id' => $planRule->sqid,
        ]);

        $response->assertRedirectBackWithErrors([
            'member_id' => 'The selected member id is invalid.',
            'cabinet_id' => 'The selected cabinet id is invalid.',
            'pickup_facility_id' => 'The selected pickup facility id is invalid.',
            'received_facility_id' => 'The selected received facility id is invalid.',
            'service_plan_id' => 'The selected service plan id is invalid.',
            'plan_rule_id' =>
                'The selected plan rule id is invalid.',
        ]);
    });
});
```
