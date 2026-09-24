# Update Tests: Relations Foreign Rule

Pest PATCH update: A complete two-row dataset rejects a foreign-tenant rule whose parent belongs either to the current tenant or to another tenant. Preserve both ownership graphs and the exact field error.

## Rejects a newly assigned relation from another tenant: plan_rule_id

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\patch;

use App\Actions\WorkOrders\UpdateWorkOrder;
use App\Models\PlanRule;
use App\Models\ServicePlan;
use App\Models\Team;
use App\Models\WorkOrder;
use Database\Factories\ServicePlanFactory;

describe('update', function (): void {
    it('rejects a newly assigned relation from another tenant: plan_rule_id', function (bool $sameParentTeam): void {
        $workOrder = WorkOrder::factory()->createOne();
        $servicePlan = ServicePlan::factory()
            ->when($sameParentTeam, fn (ServicePlanFactory $factory): ServicePlanFactory => $factory->recycle($workOrder->team))
            ->createOne();
        $planRule = PlanRule::factory()
            ->for(Team::factory())
            ->for($servicePlan)
            ->createOne();

        login(team: $workOrder->team);

        mock(UpdateWorkOrder::class)
            ->shouldNotReceive('handle');

        $response = patch(route('teams.work-orders.update', [
            'team' => $workOrder->team,
            'work_order' => $workOrder,
        ]), [
            'plan_rule_id' => $planRule->sqid,
        ]);

        $response->assertRedirectBackWithErrors([
            'plan_rule_id' =>
                'The selected plan rule id is invalid.',
        ]);
    })->with([
        'different parent team' => false,
        'same parent team' => true,
    ]);
});
```
