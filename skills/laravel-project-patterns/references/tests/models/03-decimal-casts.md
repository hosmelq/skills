# Model Tests: Decimal and Integer Casts

In-memory model casts that preserve decimal strings at two and four places, convert numeric strings to integers and resolve exact backed enum cases.

Use `toBe()` for precision and type. A numeric-looking decimal string is not a float.

```php
<?php

declare(strict_types=1);

use App\Enums\CurrencyCode;
use App\Enums\LengthUnit;
use App\Enums\WeightUnit;
use App\Models\WorkOrderLine;
use Carbon\CarbonImmutable;

it('correctly casts attributes', function (int|float $value, string $expected): void {
    $workOrderLine = new WorkOrderLine([
        'created_at' => '2026-01-15 08:00:00',
        'currency_code' => 'USD',
        'unit_value' => $value,
        'deleted_at' => '2026-01-15 08:00:00',
        'dimension_unit' => 'inches',
        'height' => 3,
        'length' => 1,
        'quantity' => '2',
        'updated_at' => '2026-01-15 08:00:00',
        'weight' => 4,
        'weight_unit' => 'pounds',
        'width' => 2,
    ]);

    expect($workOrderLine)
        ->created_at->toBeInstanceOf(CarbonImmutable::class)
        ->currency_code->toBe(CurrencyCode::USD)
        ->unit_value->toBe($expected)
        ->deleted_at->toBeInstanceOf(CarbonImmutable::class)
        ->dimension_unit->toBe(LengthUnit::Inches)
        ->height->toBe('3.0000')
        ->length->toBe('1.0000')
        ->quantity->toBe(2)
        ->updated_at->toBeInstanceOf(CarbonImmutable::class)
        ->weight->toBe('4.0000')
        ->weight_unit->toBe(WeightUnit::Pounds)
        ->width->toBe('2.0000');
})->with([
    'integer amount' => [25, '25.00'],
    'fractional amount' => [1.2, '1.20'],
]);
```
