# Update Tests: Errors Relational Dependencies

Pest PATCH update: Mocked owner/selected-cabinet mismatch, rule without plan and rule belonging to another plan retain separate action exception factories and validation mappings.

The action is mocked to throw. These assertions verify controller translation, not the underlying business guard.

## Complete block

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\patch;

use App\Actions\WorkOrders\UpdateWorkOrder;
use App\Exceptions\WorkOrders\MemberDoesNotOwnCabinet;
use App\Exceptions\WorkOrders\PlanRuleDoesNotBelongToServicePlan;
use App\Exceptions\WorkOrders\PlanRuleRequiresServicePlan;
use App\Models\WorkOrder;

describe('update', function (): void {
    it('maps a relation ownership mismatch to validation', function (): void {
        $workOrder = WorkOrder::factory()->createOne();

        signIn(team: $workOrder->team);

        mock(UpdateWorkOrder::class)
            ->shouldReceive('handle')
            ->once()
            ->andThrow(MemberDoesNotOwnCabinet::becauseTheCabinetBelongsToAnotherMember());

        $response = patch(route('teams.work-orders.update', [
            'team' => $workOrder->team,
            'work_order' => $workOrder,
        ]), ['note' => 'No']);

        $response->assertRedirectBackWithErrors([
            'member_id' => 'The selected member must own the selected cabinet.',
        ]);
    });

    it('maps a missing prerequisite relation rejection to validation', function (): void {
        $workOrder = WorkOrder::factory()->createOne();

        signIn(team: $workOrder->team);

        mock(UpdateWorkOrder::class)
            ->shouldReceive('handle')
            ->once()
            ->andThrow(PlanRuleRequiresServicePlan::becauseNoneWasSelected());

        $response = patch(route('teams.work-orders.update', [
            'team' => $workOrder->team,
            'work_order' => $workOrder,
        ]), ['note' => 'No']);

        $response->assertRedirectBackWithErrors([
            'plan_rule_id' =>
                'Select a service plan before selecting a plan rule.',
        ]);
    });

    it('maps a dependent relation mismatch to validation', function (
    ): void {
        $workOrder = WorkOrder::factory()->createOne();

        signIn(team: $workOrder->team);

        mock(UpdateWorkOrder::class)
            ->shouldReceive('handle')
            ->once()
            ->andThrow(PlanRuleDoesNotBelongToServicePlan::becauseTheyDoNotMatch());

        $response = patch(route('teams.work-orders.update', [
            'team' => $workOrder->team,
            'work_order' => $workOrder,
        ]), ['note' => 'No']);

        $response->assertRedirectBackWithErrors([
            'plan_rule_id' =>
                'The selected plan rule does not belong to the selected service plan.',
        ]);
    });
});
```
