# Action Tests: Observe Create Query Order

Integration action tests: Observe plan row-lock SQL before rule resolution and insertion, plus one additional transaction level at the write and returned relation identities.

Register the [query observer](01-row-lock-observation.md) before the action. SQL fragments and candidate bindings prove observed order and transaction depth, not exact row identity, lock duration or contention.

```php
<?php

declare(strict_types=1);

use App\Actions\WorkOrders\CreateWorkOrder;
use App\Actions\WorkOrders\Inputs\CreateWorkOrderInput;
use App\Models\PlanRule;
use App\Models\ServicePlan;
use App\Models\Team;
use App\Models\WorkOrder;
use App\Models\WorkOrderStatus;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;

it('observes lock SQL before resolution and insertion', function (): void {
    $team = Team::factory()->createOne();
    WorkOrderStatus::factory()->initial()->recycle($team)->createOne();
    $servicePlan = ServicePlan::factory()->recycle($team)->createOne();
    $planRule = PlanRule::factory()->recycle($servicePlan)->createOne();
    $steps = [];
    $writeTransactionLevels = [];
    $transactionLevelBeforeCreate = DB::transactionLevel();

    assertRowLockQueryObserved($servicePlan);
    DB::listen(function (QueryExecuted $query) use (&$steps, $planRule, $servicePlan): void {
        if (
            str_contains($query->sql, '"service_plans"')
            && str_contains($query->sql, 'for update')
            && in_array($servicePlan->id, $query->bindings, true)
        ) {
            $steps[] = 'service-plan-lock';

            return;
        }

        if (
            str_contains($query->sql, '"plan_rules"')
            && in_array($planRule->id, $query->bindings, true)
        ) {
            $steps[] = 'plan-rule-resolution';

            return;
        }

        if (str_starts_with($query->sql, 'insert into "work_orders"')) {
            $steps[] = 'record-write';

            return;
        }
    });
    WorkOrder::creating(function () use (&$writeTransactionLevels): void {
        $writeTransactionLevels['workOrder'] = DB::transactionLevel();
    });

    $workOrder = resolve(CreateWorkOrder::class)->handle($team, CreateWorkOrderInput::from([
        'plan_rule_id' => $planRule->id,
        'service_plan_id' => $servicePlan->id,
    ]));

    expect($steps)->toBe([
        'service-plan-lock',
        'plan-rule-resolution',
        'record-write',
    ])->and($writeTransactionLevels)->toBe([
        'workOrder' => $transactionLevelBeforeCreate + 1,
    ])->and($workOrder->servicePlan?->is($servicePlan))->toBeTrue()
        ->and($workOrder->planRule?->is($planRule))->toBeTrue();
});
```
