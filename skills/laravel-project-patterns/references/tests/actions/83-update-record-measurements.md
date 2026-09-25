# Action Tests: Update Coupled Field Guards

Integration action tests: Reject incomplete weight/unit pairs, partially added or cleared dimensions, a unit incompatible with the selected plan, and clearing a required timestamp.

```php
<?php

declare(strict_types=1);

use App\Actions\WorkOrders\Inputs\UpdateWorkOrderInput;
use App\Actions\WorkOrders\UpdateWorkOrder;
use App\Enums\LengthUnit;
use App\Enums\WeightUnit;
use App\Exceptions\WorkOrders\WeightUnitDoesNotMatchServicePlan;
use App\Exceptions\WorkOrders\WorkOrderDimensionsAreIncomplete;
use App\Exceptions\WorkOrders\WorkOrderRequiresReceivedAt;
use App\Exceptions\WorkOrders\WorkOrderWeightIsIncomplete;
use App\Models\ServicePlan;
use App\Models\Team;
use App\Models\WorkOrder;

it('rejects weight without a unit', function (): void {
    $workOrder = WorkOrder::factory()->createOne();

    expect(fn () => resolve(UpdateWorkOrder::class)->handle($workOrder, UpdateWorkOrderInput::from([
        'weight' => '1.0000',
    ])))->toThrow(
        WorkOrderWeightIsIncomplete::class,
        'Work order weight and weight unit must be provided together.',
    );
});

it('rejects a unit without weight', function (): void {
    $workOrder = WorkOrder::factory()->createOne();

    expect(fn () => resolve(UpdateWorkOrder::class)->handle($workOrder, UpdateWorkOrderInput::from([
        'weight_unit' => WeightUnit::Pounds->value,
    ])))->toThrow(
        WorkOrderWeightIsIncomplete::class,
        'Work order weight and weight unit must be provided together.',
    );
});

it('rejects incomplete dimensions', function (): void {
    $workOrder = WorkOrder::factory()->createOne();

    expect(fn () => resolve(UpdateWorkOrder::class)->handle($workOrder, UpdateWorkOrderInput::from([
        'length' => '1.0000',
    ])))->toThrow(
        WorkOrderDimensionsAreIncomplete::class,
        'Work order dimensions and dimension unit must be provided together.',
    );
});

it('rejects partially clearing dimensions', function (): void {
    $workOrder = WorkOrder::factory()->createOne([
        'dimension_unit' => LengthUnit::Inches,
        'height' => '1.0000',
        'length' => '1.0000',
        'width' => '1.0000',
    ]);

    expect(fn () => resolve(UpdateWorkOrder::class)->handle($workOrder, UpdateWorkOrderInput::from([
        'height' => null,
    ])))->toThrow(
        WorkOrderDimensionsAreIncomplete::class,
        'Work order dimensions and dimension unit must be provided together.',
    );
});

it('rejects a mismatched weight unit', function (): void {
    $team = Team::factory()->createOne();
    $servicePlan = ServicePlan::factory()->recycle($team)->createOne([
        'weight_unit' => WeightUnit::Pounds,
    ]);
    $workOrder = WorkOrder::factory()->for($team)->createOne();

    expect(fn () => resolve(UpdateWorkOrder::class)->handle($workOrder, UpdateWorkOrderInput::from([
        'service_plan_id' => $servicePlan->id,
        'weight' => '1.0000',
        'weight_unit' => WeightUnit::Kilograms->value,
    ])))->toThrow(
        WeightUnitDoesNotMatchServicePlan::class,
        'The work order weight unit does not match the selected service plan.',
    );
});

it('rejects clearing a required timestamp', function (): void {
    $workOrder = WorkOrder::factory()->createOne();

    expect(fn () => resolve(UpdateWorkOrder::class)->handle($workOrder, UpdateWorkOrderInput::from([
        'received_at' => null,
    ])))->toThrow(
        WorkOrderRequiresReceivedAt::class,
        'The work order received at value is required.',
    );
});
```
