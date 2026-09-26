# Requests: Create an Address

Require a country enum and constrain the optional province by its ISO code. Nullable coordinates use reciprocal required_with; phone accepts the configured country-code values.

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\CountryCode;
use App\Models\World\State;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Propaganistas\LaravelPhone\Rules\Phone;
use Stringable;

class StoreMemberAddressRequest extends FormRequest
{
    /**
     * @return array<string, list<string|Stringable|ValidationRule>>
     */
    public function rules(): array
    {
        return [
            'address1' => ['nullable', 'string', 'max:255'],
            'address2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'country_code' => ['required', Rule::enum(CountryCode::class)],
            'first_name' => ['nullable', 'string', 'max:255'],
            'label' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:longitude'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:latitude'],
            'phone_number' => [
                'nullable',
                'string',
                'max:255',
                new Phone()->country(CountryCode::values()),
            ],
            'postal_code' => ['nullable', 'string', 'max:255'],
            'province_code' => [
                'nullable',
                'string',
                'max:3',
                Rule::exists(State::class, 'iso2')
                    ->where('country_code', $this->enum('country_code', CountryCode::class)),
            ],
        ];
    }
}
```
