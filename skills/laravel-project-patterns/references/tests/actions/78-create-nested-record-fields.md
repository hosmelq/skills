# Action Tests: Create Child Fields

Integration action tests: Persist a child with typed currency, value, quantity, dimensions and weight; assert tenant, parent and group identity plus required-only nullable defaults.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Actions\WorkOrderLines\CreateWorkOrderLine;
use App\Actions\WorkOrderLines\Inputs\CreateWorkOrderLineInput;
use App\Enums\CurrencyCode;
use App\Enums\LengthUnit;
use App\Enums\WeightUnit;
use App\Models\ItemGroup;
use App\Models\WorkOrder;
use App\Models\WorkOrderLine;

it('creates a record', function (): void {
    $workOrder = WorkOrder::factory()->createOne();
    $itemGroup = ItemGroup::factory()->for($workOrder->team)->createOne();

    $line = resolve(CreateWorkOrderLine::class)->handle(
        $workOrder,
        CreateWorkOrderLineInput::from([
            'currency_code' => CurrencyCode::USD->value,
            'description' => 'Laptop computer',
            'dimension_unit' => LengthUnit::Inches->value,
            'height' => '3.2500',
            'item_group_id' => $itemGroup->id,
            'length' => '12.5000',
            'quantity' => 2,
            'unit_value' => '123.45',
            'weight' => '5.7500',
            'weight_unit' => WeightUnit::Pounds->value,
            'width' => '8.5000',
        ]),
    );

    expect($line)->toBeInstanceOf(WorkOrderLine::class);

    assertDatabaseHas(WorkOrderLine::class, [
        'id' => $line->id,
        'item_group_id' => $itemGroup->id,
        'team_id' => $workOrder->team_id,
        'work_order_id' => $workOrder->id,
        'currency_code' => CurrencyCode::USD->value,
        'description' => 'Laptop computer',
        'dimension_unit' => LengthUnit::Inches->value,
        'height' => '3.2500',
        'length' => '12.5000',
        'quantity' => 2,
        'unit_value' => '123.45',
        'weight' => '5.7500',
        'weight_unit' => WeightUnit::Pounds->value,
        'width' => '8.5000',
    ]);
});

it('creates a record with only required fields', function (): void {
    $workOrder = WorkOrder::factory()->createOne();

    $line = resolve(CreateWorkOrderLine::class)->handle(
        $workOrder,
        CreateWorkOrderLineInput::from([
            'description' => 'Documents',
            'quantity' => 1,
        ]),
    );

    assertDatabaseHas(WorkOrderLine::class, [
        'id' => $line->id,
        'item_group_id' => null,
        'team_id' => $workOrder->team_id,
        'work_order_id' => $workOrder->id,
        'currency_code' => null,
        'description' => 'Documents',
        'dimension_unit' => null,
        'height' => null,
        'length' => null,
        'quantity' => 1,
        'unit_value' => null,
        'weight' => null,
        'weight_unit' => null,
        'width' => null,
    ]);
});
```
