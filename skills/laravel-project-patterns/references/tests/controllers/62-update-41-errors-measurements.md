# Update Tests: Errors Measurements

PATCH update: Mocked action exceptions for incomplete weight, mismatched weight unit and incomplete dimensions map to their exact validation fields; no real guard is executed.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\patch;

use App\Actions\WorkOrders\UpdateWorkOrder;
use App\Exceptions\WorkOrders\WeightUnitDoesNotMatchServicePlan;
use App\Exceptions\WorkOrders\WorkOrderDimensionsAreIncomplete;
use App\Exceptions\WorkOrders\WorkOrderWeightIsIncomplete;
use App\Models\WorkOrder;

describe('update', function (): void {
    it('maps an incomplete weight rejection to validation', function (): void {
        $workOrder = WorkOrder::factory()->createOne();

        login(team: $workOrder->team);

        mock(UpdateWorkOrder::class)
            ->shouldReceive('handle')
            ->once()
            ->andThrow(WorkOrderWeightIsIncomplete::becauseValueAndUnitMustBeProvidedTogether());

        $response = patch(route('teams.work-orders.update', [
            'team' => $workOrder->team,
            'work_order' => $workOrder,
        ]), ['note' => 'No']);

        $response->assertRedirectBackWithErrors([
            'weight_unit' => 'Work order weight and its unit must be provided together.',
        ]);
    });

    it('maps a mismatched weight unit rejection to validation', function (): void {
        $workOrder = WorkOrder::factory()->createOne();

        login(team: $workOrder->team);

        mock(UpdateWorkOrder::class)
            ->shouldReceive('handle')
            ->once()
            ->andThrow(
                WeightUnitDoesNotMatchServicePlan::becauseItDiffersFromTheServicePlan(),
            );

        $response = patch(route('teams.work-orders.update', [
            'team' => $workOrder->team,
            'work_order' => $workOrder,
        ]), ['note' => 'No']);

        $response->assertRedirectBackWithErrors([
            'weight_unit' => 'The work order weight unit must match the selected service plan.',
        ]);
    });

    it('maps incomplete dimensions to validation', function (): void {
        $workOrder = WorkOrder::factory()->createOne();

        login(team: $workOrder->team);

        mock(UpdateWorkOrder::class)
            ->shouldReceive('handle')
            ->once()
            ->andThrow(
                WorkOrderDimensionsAreIncomplete::becauseValuesAndUnitMustBeProvidedTogether(),
            );

        $response = patch(route('teams.work-orders.update', [
            'team' => $workOrder->team,
            'work_order' => $workOrder,
        ]), ['note' => 'No']);

        $response->assertRedirectBackWithErrors([
            'dimension_unit' => 'Work order dimensions and their unit must be provided together.',
        ]);
    });
});
```
