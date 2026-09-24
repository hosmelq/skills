# Model Tests: Decimal and Integer Casts

In-memory casts preserve two-place monetary strings, four-place measurements, integer quantity, unit enums and immutable timestamps.

Use `toBe()` for precision and type; a decimal string is not a float.

```php
<?php

declare(strict_types=1);

use App\Enums\CurrencyCode;
use App\Enums\LengthUnit;
use App\Enums\WeightUnit;
use App\Models\WorkOrderLine;
use Carbon\CarbonImmutable;

it('correctly casts attributes', function (): void {
    $workOrderLine = new WorkOrderLine([
        'created_at' => '2026-01-15 08:00:00',
        'currency_code' => 'USD',
        'deleted_at' => '2026-01-15 08:00:00',
        'dimension_unit' => 'inches',
        'height' => 3,
        'length' => 1,
        'quantity' => '2',
        'unit_value' => 25,
        'updated_at' => '2026-01-15 08:00:00',
        'weight' => 4,
        'weight_unit' => 'pounds',
        'width' => 2,
    ]);

    expect($workOrderLine)
        ->created_at->toBeInstanceOf(CarbonImmutable::class)
        ->currency_code->toBe(CurrencyCode::USD)
        ->deleted_at->toBeInstanceOf(CarbonImmutable::class)
        ->dimension_unit->toBe(LengthUnit::Inches)
        ->height->toBe('3.0000')
        ->length->toBe('1.0000')
        ->quantity->toBe(2)
        ->unit_value->toBe('25.00')
        ->updated_at->toBeInstanceOf(CarbonImmutable::class)
        ->weight->toBe('4.0000')
        ->weight_unit->toBe(WeightUnit::Pounds)
        ->width->toBe('2.0000');
});
```
