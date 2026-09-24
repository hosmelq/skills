# HTTP Resource Tests: Record Resource Fields

Exact resource JSON for decimal measurements, enum units, external reference metadata, notes, a custom event timestamp, Sqid and standard timestamps.

```php
<?php

declare(strict_types=1);

use App\Enums\LengthUnit;
use App\Enums\WeightUnit;
use App\Models\WorkOrder;

it('formats resource correctly', function (): void {
    $workOrder = WorkOrder::factory()->createOne([
        'dimension_unit' => LengthUnit::Inches,
        'external_service_name' => 'Example Service',
        'external_tracking_number' => 'DEMO-TRACK-100',
        'height' => 3.75,
        'length' => 10.25,
        'note' => 'Handle with care.',
        'opened_at' => '2026-01-15 08:00:00',
        'reference' => 'Record-100',
        'source_label_text' => 'Original label text',
        'weight' => 4.5,
        'weight_unit' => WeightUnit::Pounds,
        'width' => 6.5,
    ]);

    $resource = json_decode($workOrder->toResource()->toJson(), true);

    expect($resource)->toEqual([
        'created_at' => $workOrder->created_at->toJSON(),
        'dimension_unit' => 'inches',
        'external_service_name' => 'Example Service',
        'external_tracking_number' => 'DEMO-TRACK-100',
        'height' => '3.7500',
        'id' => $workOrder->sqid,
        'length' => '10.2500',
        'note' => 'Handle with care.',
        'opened_at' => $workOrder->opened_at->toJSON(),
        'reference' => 'Record-100',
        'source_label_text' => 'Original label text',
        'updated_at' => $workOrder->updated_at->toJSON(),
        'weight' => '4.5000',
        'weight_unit' => 'pounds',
        'width' => '6.5000',
    ]);
});
```
