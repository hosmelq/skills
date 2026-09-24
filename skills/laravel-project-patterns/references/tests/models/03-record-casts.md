# Model Tests: Measurement and Event Timestamp Casts

In-memory casts combine four-place dimensions and weight, exact unit enums, immutable standard timestamps and a separate event timestamp.

```php
<?php

declare(strict_types=1);

use App\Enums\LengthUnit;
use App\Enums\WeightUnit;
use App\Models\WorkOrder;
use Carbon\CarbonImmutable;

it('correctly casts attributes', function (): void {
    $workOrder = new WorkOrder([
        'created_at' => '2026-01-15 08:00:00',
        'deleted_at' => '2026-01-15 08:00:00',
        'dimension_unit' => 'inches',
        'height' => 3,
        'length' => 1,
        'opened_at' => '2026-01-15 08:00:00',
        'updated_at' => '2026-01-15 08:00:00',
        'weight' => 4,
        'weight_unit' => 'pounds',
        'width' => 2,
    ]);

    expect($workOrder)
        ->created_at->toBeInstanceOf(CarbonImmutable::class)
        ->deleted_at->toBeInstanceOf(CarbonImmutable::class)
        ->dimension_unit->toBe(LengthUnit::Inches)
        ->height->toBe('3.0000')
        ->length->toBe('1.0000')
        ->opened_at->toBeInstanceOf(CarbonImmutable::class)
        ->updated_at->toBeInstanceOf(CarbonImmutable::class)
        ->weight->toBe('4.0000')
        ->weight_unit->toBe(WeightUnit::Pounds)
        ->width->toBe('2.0000');
});
```
