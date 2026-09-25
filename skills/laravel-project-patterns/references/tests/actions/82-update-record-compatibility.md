# Action Tests: Update Related Selection Guards

Integration action tests: Reject an owner that does not own the assignment, a rule without its plan, and a rule from a different plan.

```php
<?php

declare(strict_types=1);

use App\Actions\WorkOrders\Inputs\UpdateWorkOrderInput;
use App\Actions\WorkOrders\UpdateWorkOrder;
use App\Exceptions\WorkOrders\MemberDoesNotOwnCabinet;
use App\Exceptions\WorkOrders\PlanRuleDoesNotBelongToServicePlan;
use App\Exceptions\WorkOrders\PlanRuleRequiresServicePlan;
use App\Models\Cabinet;
use App\Models\Member;
use App\Models\PlanRule;
use App\Models\ServicePlan;
use App\Models\Team;
use App\Models\WorkOrder;

it('rejects an owner that does not own the selected assignment', function (): void {
    $team = Team::factory()->createOne();
    $workOrder = WorkOrder::factory()->for($team)->createOne();
    $cabinet = Cabinet::factory()->recycle($team)->createOne();
    $member = Member::factory()->recycle($team)->createOne();

    expect(fn () => resolve(UpdateWorkOrder::class)->handle($workOrder, UpdateWorkOrderInput::from([
        'cabinet_id' => $cabinet->id,
        'member_id' => $member->id,
    ])))->toThrow(
        MemberDoesNotOwnCabinet::class,
        'The selected member does not own the selected cabinet.',
    );
});

it('rejects a rule without a plan', function (): void {
    $workOrder = WorkOrder::factory()->createOne();
    $planRule = PlanRule::factory()->createOne();

    expect(fn () => resolve(UpdateWorkOrder::class)->handle($workOrder, UpdateWorkOrderInput::from([
        'plan_rule_id' => $planRule->id,
    ])))->toThrow(
        PlanRuleRequiresServicePlan::class,
        'A service plan is required when selecting a plan rule.',
    );
});

it('rejects a rule from another plan', function (): void {
    $team = Team::factory()->createOne();
    $workOrder = WorkOrder::factory()->for($team)->createOne();
    $servicePlan = ServicePlan::factory()->recycle($team)->createOne();
    $otherPlanRule = PlanRule::factory()->recycle($team)->createOne();

    expect(fn () => resolve(UpdateWorkOrder::class)->handle($workOrder, UpdateWorkOrderInput::from([
        'plan_rule_id' => $otherPlanRule->id,
        'service_plan_id' => $servicePlan->id,
    ])))->toThrow(
        PlanRuleDoesNotBelongToServicePlan::class,
        'The selected plan rule does not belong to the selected service plan.',
    );
});
```
