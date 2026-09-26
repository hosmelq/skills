# Requests: Update Optional Settings

Omitted settings remain absent. Supplied required settings reject null; nullable contact fields and prefix can be cleared. Keep numeric length validation distinct from integer validation.

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\AssignmentMode;
use App\Enums\CodeAlphabet;
use App\Enums\CountryCode;
use App\Enums\UnitSystem;
use App\Enums\WeightUnit;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Propaganistas\LaravelPhone\Rules\Phone;
use Stringable;

class UpdateTeamRequest extends FormRequest
{
    /**
     * @return array<string, list<string|Stringable|ValidationRule>>
     */
    public function rules(): array
    {
        return [
            'assignment_mode' => [
                'sometimes',
                'required',
                Rule::enum(AssignmentMode::class),
            ],
            'cabinets_enabled' => ['sometimes', 'required', 'boolean'],
            'code_format_alphabet_type' => [
                'sometimes',
                'required',
                Rule::enum(CodeAlphabet::class),
            ],
            'code_format_length' => [
                'sometimes',
                'required',
                'numeric',
                'min:4',
                'max:20',
            ],
            'code_format_prefix' => ['nullable', 'string', 'max:20'],
            'contact_email' => [
                'nullable',
                'string',
                'max:255',
                'email:strict,dns',
                'indisposable',
            ],
            'contact_phone_number' => [
                'nullable',
                'string',
                'max:255',
                new Phone()->country(CountryCode::values()),
            ],
            'country_code' => ['sometimes', 'required', Rule::enum(CountryCode::class)],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'timezone' => ['sometimes', 'required', 'string', 'timezone'],
            'unit_system' => ['sometimes', 'required', Rule::enum(UnitSystem::class)],
            'weight_unit' => ['sometimes', 'required', Rule::enum(WeightUnit::class)],
            'work_orders_enabled' => ['sometimes', 'required', 'boolean'],
        ];
    }
}
```
