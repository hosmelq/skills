# Requests: Create Paired and Grouped Values

Require complete currency/value, weight/unit and dimension groups when any companion is filled. Zero declared value is valid; measurements must be positive. The optional group must be active, nontrashed and tenant-owned.

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\CurrencyCode;
use App\Enums\LengthUnit;
use App\Enums\WeightUnit;
use App\Models\ItemGroup;
use App\Models\WorkOrder;
use Illuminate\Container\Attributes\RouteParameter;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Stringable;

class StoreWorkOrderLineRequest extends FormRequest
{
    /**
     * @return array<string, list<string|Stringable|ValidationRule>>
     */
    public function rules(#[RouteParameter('work_order')] WorkOrder $workOrder): array
    {
        return [
            'currency_code' => [
                'nullable',
                'required_with:unit_value',
                Rule::enum(CurrencyCode::class),
            ],
            'description' => ['required', 'string', 'max:2000'],
            'dimension_unit' => [
                'nullable',
                'required_with:height,length,width',
                Rule::enum(LengthUnit::class),
            ],
            'height' => [
                'nullable',
                'required_with:dimension_unit,length,width',
                'decimal:0,4',
                'gt:0',
                'max:9999.9999',
            ],
            'item_group_id' => [
                'nullable',
                Rule::exists(ItemGroup::class, 'id')
                    ->where('team_id', $workOrder->team_id)
                    ->whereNull('deactivated_at')
                    ->withoutTrashed(),
            ],
            'length' => [
                'nullable',
                'required_with:dimension_unit,height,width',
                'decimal:0,4',
                'gt:0',
                'max:9999.9999',
            ],
            'quantity' => ['required', 'integer', 'min:1', 'max:2147483647'],
            'unit_value' => [
                'nullable',
                'required_with:currency_code',
                'decimal:0,2',
                'gte:0',
                'max:999999.99',
            ],
            'weight' => [
                'nullable',
                'required_with:weight_unit',
                'decimal:0,4',
                'gt:0',
                'max:9999.9999',
            ],
            'weight_unit' => [
                'nullable',
                'required_with:weight',
                Rule::enum(WeightUnit::class),
            ],
            'width' => [
                'nullable',
                'required_with:dimension_unit,height,length',
                'decimal:0,4',
                'gt:0',
                'max:9999.9999',
            ],
        ];
    }
}
```
