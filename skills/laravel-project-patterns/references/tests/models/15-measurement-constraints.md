# Model Tests: Measurement Constraints

Database checks for strictly positive quantity, a positive weight with its unit, and a complete positive dimensions group. Keeps every missing, null, zero and negative dataset row.

The factory leaves optional measurements null. Each dataset row violates only its named constraint; preserve the complete row rather than applying a blanket invalid-value list.

```php
<?php

declare(strict_types=1);

use App\Enums\LengthUnit;
use App\Enums\WeightUnit;
use App\Models\WorkOrderLine;
use Illuminate\Database\QueryException;

it('enforces positive quantity at the database level', function (int $quantity): void {
    expect(fn () => WorkOrderLine::factory()->createOne([
        'quantity' => $quantity,
    ]))->toThrow(QueryException::class, 'work_order_lines_quantity_check');
})->with([
    'zero' => 0,
    'negative' => -1,
]);

it('enforces the weight and unit pair at the database level', function (array $attributes): void {
    expect(fn () => WorkOrderLine::factory()->createOne($attributes))
        ->toThrow(QueryException::class, 'work_order_lines_weight_pair_check');
})->with([
    'weight without unit' => [[
        'weight' => 1,
        'weight_unit' => null,
    ]],
    'unit without weight' => [[
        'weight' => null,
        'weight_unit' => WeightUnit::Pounds,
    ]],
    'zero weight' => [[
        'weight' => 0,
        'weight_unit' => WeightUnit::Pounds,
    ]],
    'negative weight' => [[
        'weight' => -1,
        'weight_unit' => WeightUnit::Pounds,
    ]],
]);

it('enforces the complete dimensions group at the database level', function (array $attributes): void {
    expect(fn () => WorkOrderLine::factory()->createOne($attributes))
        ->toThrow(QueryException::class, 'work_order_lines_dimensions_group_check');
})->with([
    'length without the remaining dimensions' => [[
        'length' => 1,
    ]],
    'dimensions without unit' => [[
        'height' => 1,
        'length' => 1,
        'width' => 1,
    ]],
    'unit without dimensions' => [[
        'dimension_unit' => LengthUnit::Inches,
    ]],
    'missing dimension with unit' => [[
        'dimension_unit' => LengthUnit::Inches,
        'height' => 1,
        'length' => 1,
    ]],
    'zero dimension' => [[
        'dimension_unit' => LengthUnit::Inches,
        'height' => 1,
        'length' => 1,
        'width' => 0,
    ]],
    'negative dimension' => [[
        'dimension_unit' => LengthUnit::Inches,
        'height' => -1,
        'length' => 1,
        'width' => 1,
    ]],
]);
```
