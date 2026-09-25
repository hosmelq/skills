# Action Tests: Update Coupled Selections

Integration action tests: Clear only the rule while preserving its plan, preserve omitted selections, preserve explicitly unchanged selections, and clear both selections together.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Actions\WorkOrders\Inputs\UpdateWorkOrderInput;
use App\Actions\WorkOrders\UpdateWorkOrder;
use App\Models\PlanRule;
use App\Models\ServicePlan;
use App\Models\Team;
use App\Models\WorkOrder;

it('clears the rule while preserving its plan', function (): void {
    $team = Team::factory()->createOne();
    $workOrder = WorkOrder::factory()->withPlanRule()->for($team)->createOne();

    resolve(UpdateWorkOrder::class)->handle($workOrder, UpdateWorkOrderInput::from([
        'plan_rule_id' => null,
    ]));

    assertDatabaseHas(WorkOrder::class, [
        'id' => $workOrder->id,
        'plan_rule_id' => null,
        'service_plan_id' => $workOrder->servicePlan->id,
    ]);
});

it('preserves omitted selections', function (): void {
    $servicePlan = ServicePlan::factory()->createOne();
    $planRule = PlanRule::factory()->recycle($servicePlan)->createOne();
    $workOrder = WorkOrder::factory()->for($servicePlan->team)->createOne([
        'plan_rule_id' => $planRule->id,
        'service_plan_id' => $servicePlan->id,
    ]);

    resolve(UpdateWorkOrder::class)->handle($workOrder, UpdateWorkOrderInput::from([
        'note' => 'Selections omitted',
    ]));

    assertDatabaseHas(WorkOrder::class, [
        'id' => $workOrder->id,
        'plan_rule_id' => $planRule->id,
        'service_plan_id' => $servicePlan->id,
        'note' => 'Selections omitted',
    ]);
});

it('preserves explicitly unchanged selections', function (): void {
    $servicePlan = ServicePlan::factory()->createOne();
    $planRule = PlanRule::factory()->recycle($servicePlan)->createOne();
    $workOrder = WorkOrder::factory()->for($servicePlan->team)->createOne([
        'plan_rule_id' => $planRule->id,
        'service_plan_id' => $servicePlan->id,
    ]);

    resolve(UpdateWorkOrder::class)->handle($workOrder, UpdateWorkOrderInput::from([
        'plan_rule_id' => $planRule->id,
        'service_plan_id' => $servicePlan->id,
    ]));

    assertDatabaseHas(WorkOrder::class, [
        'id' => $workOrder->id,
        'plan_rule_id' => $planRule->id,
        'service_plan_id' => $servicePlan->id,
    ]);
});

it('clears both selections together', function (): void {
    $servicePlan = ServicePlan::factory()->createOne();
    $planRule = PlanRule::factory()->recycle($servicePlan)->createOne();
    $workOrder = WorkOrder::factory()->for($servicePlan->team)->createOne([
        'plan_rule_id' => $planRule->id,
        'service_plan_id' => $servicePlan->id,
    ]);

    resolve(UpdateWorkOrder::class)->handle($workOrder, UpdateWorkOrderInput::from([
        'plan_rule_id' => null,
        'service_plan_id' => null,
    ]));

    assertDatabaseHas(WorkOrder::class, [
        'id' => $workOrder->id,
        'plan_rule_id' => null,
        'service_plan_id' => null,
    ]);
});
```
