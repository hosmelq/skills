# Update Tests: Relations Clearing Plan And Rule

PATCH update: Clearing a plan while omitting its stored rule maps a mocked domain exception. Submitting empty strings for both fields maps two null DTO values to the action. Neither case asserts persistence.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\patch;

use App\Actions\WorkOrders\Inputs\UpdateWorkOrderInput;
use App\Actions\WorkOrders\UpdateWorkOrder;
use App\Exceptions\WorkOrders\PlanRuleRequiresServicePlan;
use App\Models\WorkOrder;
use Spatie\LaravelData\Optional;

describe('update', function (): void {
    it('maps a cleared prerequisite rejection to validation when the dependent field is omitted', function (): void {
        $workOrder = WorkOrder::factory()->withPlanRule()->createOne();

        login(team: $workOrder->team);

        mock(UpdateWorkOrder::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(
                fn (WorkOrder $workOrderArgument, UpdateWorkOrderInput $input): bool => $workOrderArgument
                    ->is($workOrder)
                    && $input->servicePlanId === null
                    && $input->planRuleId instanceof Optional,
            )
            ->andThrow(PlanRuleRequiresServicePlan::becauseNoneWasSelected());

        $response = patch(route('teams.work-orders.update', [
            'team' => $workOrder->team,
            'work_order' => $workOrder,
        ]), [
            'service_plan_id' => '',
        ]);

        $response->assertRedirectBackWithErrors([
            'plan_rule_id' =>
                'Select a service plan before selecting a plan rule.',
        ]);
    });

    it('clears a relation and its dependent relation when both are empty', function (): void {
        $workOrder = WorkOrder::factory()->withPlanRule()->createOne();

        login(team: $workOrder->team);

        mock(UpdateWorkOrder::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(
                fn (WorkOrder $workOrderArgument, UpdateWorkOrderInput $input): bool => $workOrderArgument
                    ->is($workOrder)
                    && $input->servicePlanId === null
                    && $input->planRuleId === null,
            );

        $response = patch(route('teams.work-orders.update', [
            'team' => $workOrder->team,
            'work_order' => $workOrder,
        ]), [
            'service_plan_id' => '',
            'plan_rule_id' => '',
        ]);

        $response->assertRedirectToRoute('teams.work-orders.show', [
            'team' => $workOrder->team,
            'work_order' => $workOrder,
        ])->assertToast('Work order updated');
    });
});
```
