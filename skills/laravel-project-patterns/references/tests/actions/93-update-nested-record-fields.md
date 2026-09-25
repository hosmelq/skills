# Action Tests: Update Child Fields

Integration action tests: Update every typed child field including a changed currency and relation; assert returned identity. Partial input preserves the full omitted field vector.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Actions\WorkOrderLines\Inputs\UpdateWorkOrderLineInput;
use App\Actions\WorkOrderLines\UpdateWorkOrderLine;
use App\Enums\CurrencyCode;
use App\Enums\LengthUnit;
use App\Enums\WeightUnit;
use App\Models\ItemGroup;
use App\Models\WorkOrderLine;

it('updates a record', function (): void {
    $line = WorkOrderLine::factory()
        ->withItemGroup()
        ->withUnitValue(CurrencyCode::USD)
        ->withDimensions()
        ->withWeight()
        ->createOne();
    $itemGroup = ItemGroup::factory()->for($line->team)->createOne();

    $updatedLine = resolve(UpdateWorkOrderLine::class)->handle(
        $line,
        UpdateWorkOrderLineInput::from([
            'currency_code' => CurrencyCode::CAD->value,
            'description' => 'Updated contents',
            'dimension_unit' => LengthUnit::Centimeters->value,
            'height' => '30.0000',
            'item_group_id' => $itemGroup->id,
            'length' => '40.0000',
            'quantity' => 3,
            'unit_value' => '999.99',
            'weight' => '4.5000',
            'weight_unit' => WeightUnit::Kilograms->value,
            'width' => '20.0000',
        ]),
    );

    expect($updatedLine->is($line))->toBeTrue();

    assertDatabaseHas(WorkOrderLine::class, [
        'id' => $line->id,
        'item_group_id' => $itemGroup->id,
        'team_id' => $line->team_id,
        'currency_code' => CurrencyCode::CAD->value,
        'description' => 'Updated contents',
        'dimension_unit' => LengthUnit::Centimeters->value,
        'height' => '30.0000',
        'length' => '40.0000',
        'quantity' => 3,
        'unit_value' => '999.99',
        'weight' => '4.5000',
        'weight_unit' => WeightUnit::Kilograms->value,
        'width' => '20.0000',
    ]);
});

it('updates only provided fields', function (): void {
    $line = WorkOrderLine::factory()->withItemGroup()->createOne([
        'currency_code' => CurrencyCode::USD,
        'description' => 'Before',
        'dimension_unit' => LengthUnit::Inches,
        'height' => '3.2500',
        'length' => '12.5000',
        'quantity' => 2,
        'unit_value' => '123.45',
        'weight' => '5.7500',
        'weight_unit' => WeightUnit::Pounds,
        'width' => '8.5000',
    ]);

    resolve(UpdateWorkOrderLine::class)->handle(
        $line,
        UpdateWorkOrderLineInput::from(['description' => 'After']),
    );

    assertDatabaseHas(WorkOrderLine::class, [
        'id' => $line->id,
        'item_group_id' => $line->item_group_id,
        'team_id' => $line->team_id,
        'currency_code' => CurrencyCode::USD->value,
        'description' => 'After',
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
```
