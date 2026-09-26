# Requests: Update Paired and Grouped Values

For each touched group, merge only omitted companions from the bound line before validation. Preserve explicit nulls. The current deactivated group remains selectable, but withoutTrashed still excludes a deleted group; new groups must be active.

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\CurrencyCode;
use App\Enums\LengthUnit;
use App\Enums\WeightUnit;
use App\Models\ItemGroup;
use App\Models\WorkOrderLine;
use Illuminate\Container\Attributes\RouteParameter;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Override;
use Stringable;

class UpdateWorkOrderLineRequest extends FormRequest
{
    /**
     * @return array<string, list<string|Stringable|ValidationRule>>
     */
    public function rules(#[RouteParameter('line')] WorkOrderLine $line): array
    {
        return [
            'currency_code' => [
                'sometimes',
                'nullable',
                'required_with:unit_value',
                Rule::enum(CurrencyCode::class),
            ],
            'description' => ['sometimes', 'required', 'string', 'max:2000'],
            'dimension_unit' => [
                'sometimes',
                'nullable',
                'required_with:height,length,width',
                Rule::enum(LengthUnit::class),
            ],
            'height' => [
                'sometimes',
                'nullable',
                'required_with:dimension_unit,length,width',
                'decimal:0,4',
                'gt:0',
                'max:9999.9999',
            ],
            'item_group_id' => [
                'sometimes',
                'nullable',
                Rule::exists(ItemGroup::class, 'id')
                    ->where('team_id', $line->team_id)
                    ->withoutTrashed()
                    ->where(function (Builder $builder) use ($line): void {
                        $builder->whereNull('deactivated_at');

                        if ($line->item_group_id !== null) {
                            $builder->orWhere('id', $line->item_group_id);
                        }
                    }),
            ],
            'length' => [
                'sometimes',
                'nullable',
                'required_with:dimension_unit,height,width',
                'decimal:0,4',
                'gt:0',
                'max:9999.9999',
            ],
            'quantity' => ['sometimes', 'required', 'integer', 'min:1', 'max:2147483647'],
            'unit_value' => [
                'sometimes',
                'nullable',
                'required_with:currency_code',
                'decimal:0,2',
                'gte:0',
                'max:999999.99',
            ],
            'weight' => [
                'sometimes',
                'nullable',
                'required_with:weight_unit',
                'decimal:0,4',
                'gt:0',
                'max:9999.9999',
            ],
            'weight_unit' => [
                'sometimes',
                'nullable',
                'required_with:weight',
                Rule::enum(WeightUnit::class),
            ],
            'width' => [
                'sometimes',
                'nullable',
                'required_with:dimension_unit,height,length',
                'decimal:0,4',
                'gt:0',
                'max:9999.9999',
            ],
        ];
    }

    #[Override]
    protected function prepareForValidation(): void
    {
        $line = $this->line();

        if ($this->hasAny(['currency_code', 'unit_value'])) {
            $this->mergeIfMissing([
                'currency_code' => $line->currency_code?->value,
                'unit_value' => $line->unit_value,
            ]);
        }

        if ($this->hasAny(['weight', 'weight_unit'])) {
            $this->mergeIfMissing([
                'weight' => $line->weight,
                'weight_unit' => $line->weight_unit?->value,
            ]);
        }

        if ($this->hasAny(['dimension_unit', 'height', 'length', 'width'])) {
            $this->mergeIfMissing([
                'dimension_unit' => $line->dimension_unit?->value,
                'height' => $line->height,
                'length' => $line->length,
                'width' => $line->width,
            ]);
        }
    }

    private function line(): WorkOrderLine
    {
        $line = $this->route('line');

        assert($line instanceof WorkOrderLine);

        return $line;
    }
}
```
