# Factories: Configure Measurements and Value

Start grouped measurements and monetary value as null. Each state supplies its unit with the value. Create and associate an optional group after persistence, using the parent record's team. That callback reads the ordinary parent relationship; the parent must be visible to its scopes.

```php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CurrencyCode;
use App\Enums\LengthUnit;
use App\Enums\WeightUnit;
use App\Models\ItemGroup;
use App\Models\WorkOrder;
use App\Models\WorkOrderLine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkOrderLine>
 */
class WorkOrderLineFactory extends Factory
{
    public function definition(): array
    {
        return [
            'work_order_id' => WorkOrder::factory(),
            'team_id' => $this->teamIdFor(...),
            'item_group_id' => null,

            'currency_code' => null,
            'description' => fake()->sentence(),
            'dimension_unit' => null,
            'height' => null,
            'length' => null,
            'quantity' => fake()->numberBetween(1, 5),
            'unit_value' => null,
            'weight' => null,
            'weight_unit' => null,
            'width' => null,
        ];
    }

    public function withDimensions(LengthUnit $dimensionUnit = LengthUnit::Inches): static
    {
        return $this->state([
            'dimension_unit' => $dimensionUnit,
            'height' => 1,
            'length' => 1,
            'width' => 1,
        ]);
    }

    public function withItemGroup(): static
    {
        return $this->afterCreating(function (WorkOrderLine $workOrderLine): void {
            $itemGroup = ItemGroup::factory()->createOne([
                'team_id' => $workOrderLine->workOrder->team_id,
            ]);

            $workOrderLine->itemGroup()->associate($itemGroup);
            $workOrderLine->save();
        });
    }

    public function withUnitValue(
        CurrencyCode $currencyCode = CurrencyCode::USD,
        float $unitValue = 1,
    ): static {
        return $this->state([
            'currency_code' => $currencyCode,
            'unit_value' => $unitValue,
        ]);
    }

    public function withWeight(WeightUnit $weightUnit = WeightUnit::Pounds): static
    {
        return $this->state([
            'weight' => 1,
            'weight_unit' => $weightUnit,
        ]);
    }

    /**
     * @param array{work_order_id: int} $attributes
     */
    private function teamIdFor(array $attributes): int
    {
        $workOrderId = $attributes['work_order_id'];

        return WorkOrder::query()
            ->withTrashed()
            ->findOrFail($workOrderId)
            ->team_id;
    }
}
```
