# Action Tests: Clear Nullable Child Fields

Integration action tests: Clear the optional child group, currency, value, dimensions and weight with explicit null input; assert all corresponding persisted fields.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Actions\WorkOrderLines\Inputs\UpdateWorkOrderLineInput;
use App\Actions\WorkOrderLines\UpdateWorkOrderLine;
use App\Models\WorkOrderLine;

it('clears nullable fields', function (): void {
    $line = WorkOrderLine::factory()
        ->withItemGroup()
        ->withUnitValue()
        ->withDimensions()
        ->withWeight()
        ->createOne();

    resolve(UpdateWorkOrderLine::class)->handle(
        $line,
        UpdateWorkOrderLineInput::from([
            'currency_code' => null,
            'dimension_unit' => null,
            'height' => null,
            'item_group_id' => null,
            'length' => null,
            'unit_value' => null,
            'weight' => null,
            'weight_unit' => null,
            'width' => null,
        ]),
    );

    assertDatabaseHas(WorkOrderLine::class, [
        'id' => $line->id,
        'item_group_id' => null,
        'currency_code' => null,
        'dimension_unit' => null,
        'height' => null,
        'length' => null,
        'unit_value' => null,
        'weight' => null,
        'weight_unit' => null,
        'width' => null,
    ]);
});
```
