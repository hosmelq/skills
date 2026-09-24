# Model Tests: Line Resource and Optional Relation

Exact model resource JSON for decimal measurements and value, integer quantity, units and currency; a loaded related group serializes its full resource, while an explicitly loaded absent group appears as null.

A loaded missing relation differs from an unloaded relation. Keep `load()` in the null case.

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
        'unit_value' => 25.5,
        'description' => 'Wireless headphones',
        'dimension_unit' => LengthUnit::Centimeters,
        'height' => 8.75,
        'length' => 20.5,
        'quantity' => 2,
        'weight' => 1.25,
        'weight_unit' => WeightUnit::Kilograms,
        'width' => 16.25,
    ]);

    $resource = json_decode($workOrderLine->toResource()->toJson(), true);

    expect($resource)->toEqual([
        'created_at' => $workOrderLine->created_at->toJSON(),
        'currency_code' => 'USD',
        'unit_value' => '25.50',
        'description' => 'Wireless headphones',
        'dimension_unit' => 'centimeters',
        'height' => '8.7500',
        'id' => $workOrderLine->public_id,
        'length' => '20.5000',
        'quantity' => 2,
        'updated_at' => $workOrderLine->updated_at->toJSON(),
        'weight' => '1.2500',
        'weight_unit' => 'kilograms',
        'width' => '16.2500',
    ]);
});

it('includes loaded relationship resources', function (): void {
    $workOrderLine = WorkOrderLine::factory()->withItemGroup()->createOne();

    $itemGroupResource = json_decode(
        $workOrderLine->itemGroup->toResource()->toJson(),
        true,
    );

    $resource = json_decode($workOrderLine->toResource()->toJson(), true);

    expect($resource)->item_group->toEqual($itemGroupResource);
});

it('includes null when a loaded relation is absent', function (): void {
    $workOrderLine = WorkOrderLine::factory()->createOne();
    $workOrderLine->load('itemGroup');

    $resource = json_decode($workOrderLine->toResource()->toJson(), true);

    expect($resource)->toHaveKey('item_group', null);
});
```
