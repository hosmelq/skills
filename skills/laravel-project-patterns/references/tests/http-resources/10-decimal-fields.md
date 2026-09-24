# HTTP Resource Tests: Decimal Fields and Integer Quantity

Exact resource JSON preserves a monetary value as two decimal places, measurements as four-place strings, integer quantity, unit enums, Sqid and timestamps.

```php
<?php

declare(strict_types=1);

use App\Enums\CurrencyCode;
use App\Enums\LengthUnit;
use App\Enums\WeightUnit;
use App\Models\WorkOrderLine;

it('formats resource correctly', function (): void {
    $workOrderLine = WorkOrderLine::factory()->createOne([
        'currency_code' => CurrencyCode::USD,
        'description' => 'Wireless headphones',
        'dimension_unit' => LengthUnit::Centimeters,
        'height' => 8.75,
        'length' => 20.5,
        'quantity' => 2,
        'unit_value' => 25.5,
        'weight' => 1.25,
        'weight_unit' => WeightUnit::Kilograms,
        'width' => 16.25,
    ]);

    $resource = json_decode($workOrderLine->toResource()->toJson(), true);

    expect($resource)->toEqual([
        'created_at' => $workOrderLine->created_at->toJSON(),
        'currency_code' => 'USD',
        'description' => 'Wireless headphones',
        'dimension_unit' => 'centimeters',
        'height' => '8.7500',
        'id' => $workOrderLine->sqid,
        'length' => '20.5000',
        'quantity' => 2,
        'unit_value' => '25.50',
        'updated_at' => $workOrderLine->updated_at->toJSON(),
        'weight' => '1.2500',
        'weight_unit' => 'kilograms',
        'width' => '16.2500',
    ]);
});
```
