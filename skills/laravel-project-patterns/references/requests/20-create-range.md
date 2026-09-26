# Requests: Create a Decimal Range

The nullable maximum must exceed the minimum; the minimum and rate allow zero. Decimal precision differs for bounds and price. Conditional lower-bound comparison runs only with a filled maximum.

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ConditionalRules;
use Illuminate\Validation\Rule;
use Stringable;

class StorePlanRateRequest extends FormRequest
{
    /**
     * @return array<string, list<ConditionalRules|string|Stringable>>
     */
    public function rules(): array
    {
        return [
            'maximum_weight' => ['nullable', 'decimal:0,4', 'gt:minimum_weight'],
            'minimum_weight' => [
                'required',
                'decimal:0,4',
                'gte:0',
                Rule::when($this->filled('maximum_weight'), 'lte:maximum_weight'),
            ],
            'name' => ['required', 'string', 'max:255'],
            'rate' => ['required', 'decimal:0,2', 'gte:0'],
        ];
    }
}
```
