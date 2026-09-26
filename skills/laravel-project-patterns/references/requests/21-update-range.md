# Requests: Update Effective Decimal Bounds

A filled minimum uses the stored non-null maximum when omitted. Any supplied maximum key, including null, fills a missing minimum. mergeIfMissing preserves explicit nulls, and merged companions enter validated output.

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\PlanRate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ConditionalRules;
use Illuminate\Validation\Rule;
use Override;
use Stringable;

class UpdatePlanRateRequest extends FormRequest
{
    /**
     * @return array<string, list<ConditionalRules|string|Stringable>>
     */
    public function rules(): array
    {
        return [
            'maximum_weight' => [
                'sometimes',
                'nullable',
                'decimal:0,4',
                'gt:minimum_weight',
            ],
            'minimum_weight' => [
                'sometimes',
                'required',
                'decimal:0,4',
                'gte:0',
                Rule::when($this->filled('maximum_weight'), 'lte:maximum_weight'),
            ],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'rate' => ['sometimes', 'required', 'decimal:0,2', 'gte:0'],
        ];
    }

    #[Override]
    protected function prepareForValidation(): void
    {
        $rate = $this->rate();

        if ($this->filled('minimum_weight') && $rate->maximum_weight !== null) {
            $this->mergeIfMissing(['maximum_weight' => $rate->maximum_weight]);
        }

        if ($this->has('maximum_weight')) {
            $this->mergeIfMissing(['minimum_weight' => $rate->minimum_weight]);
        }
    }

    private function rate(): PlanRate
    {
        $rate = $this->route('rate');

        assert($rate instanceof PlanRate);

        return $rate;
    }
}
```
