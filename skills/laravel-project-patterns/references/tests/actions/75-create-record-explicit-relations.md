# Action Tests: Create Explicit Relation Selections

Integration action tests: An assignment does not infer its owner or plan; persist explicit selections and allow an independent plan without mutating assignment configuration.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Actions\WorkOrders\CreateWorkOrder;
use App\Actions\WorkOrders\Inputs\CreateWorkOrderInput;
use App\Models\Cabinet;
use App\Models\Member;
use App\Models\ServicePlan;
use App\Models\Team;
use App\Models\WorkOrder;
use App\Models\WorkOrderStatus;

it('does not infer owner or plan from an assignment', function (): void {
    $team = Team::factory()->createOne();
    WorkOrderStatus::factory()->initial()->recycle($team)->createOne();
    $cabinet = Cabinet::factory()->recycle($team)->createOne();

    $workOrder = resolve(CreateWorkOrder::class)->handle($team, CreateWorkOrderInput::from([
        'cabinet_id' => $cabinet->id,
    ]));

    assertDatabaseHas(WorkOrder::class, [
        'id' => $workOrder->id,
        'cabinet_id' => $cabinet->id,
        'member_id' => null,
        'service_plan_id' => null,
    ]);
});

it('persists explicitly selected assignment relations', function (): void {
    $team = Team::factory()->createOne();
    WorkOrderStatus::factory()->initial()->recycle($team)->createOne();
    $member = Member::factory()->recycle($team)->createOne();
    $servicePlan = ServicePlan::factory()->recycle($team)->createOne();
    $cabinet = Cabinet::factory()->for($member)->recycle($team)->recycle($servicePlan)->createOne();

    $workOrder = resolve(CreateWorkOrder::class)->handle($team, CreateWorkOrderInput::from([
        'cabinet_id' => $cabinet->id,
        'member_id' => $cabinet->member->id,
        'service_plan_id' => $cabinet->servicePlan->id,
    ]));

    assertDatabaseHas(WorkOrder::class, [
        'id' => $workOrder->id,
        'cabinet_id' => $cabinet->id,
        'member_id' => $member->id,
        'service_plan_id' => $servicePlan->id,
    ]);
});

it('allows an independent plan without mutating the assignment', function (): void {
    $team = Team::factory()->createOne();
    WorkOrderStatus::factory()->initial()->recycle($team)->createOne();
    $cabinet = Cabinet::factory()->recycle($team)->createOne();
    $servicePlan = ServicePlan::factory()->recycle($team)->createOne();
    $originalCabinetServicePlanId = $cabinet->service_plan_id;

    $workOrder = resolve(CreateWorkOrder::class)->handle($team, CreateWorkOrderInput::from([
        'cabinet_id' => $cabinet->id,
        'service_plan_id' => $servicePlan->id,
    ]));

    assertDatabaseHas(Cabinet::class, [
        'id' => $cabinet->id,
        'service_plan_id' => $originalCabinetServicePlanId,
    ]);
    assertDatabaseHas(WorkOrder::class, [
        'id' => $workOrder->id,
        'service_plan_id' => $servicePlan->id,
    ]);
});
```
