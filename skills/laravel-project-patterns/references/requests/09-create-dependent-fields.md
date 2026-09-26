# Requests: Create Dependent Fields

Require name, type and country; constrain province through a query closure. opening_hours uses missing, which rejects any present value, including null. Coordinates form a nullable pair.

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\CountryCode;
use App\Enums\FacilityType;
use App\Models\World\State;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Propaganistas\LaravelPhone\Rules\Phone;
use Stringable;

class StoreFacilityRequest extends FormRequest
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
            'country_code' => ['required', Rule::enum(CountryCode::class)],
            'latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:longitude'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:latitude'],
            'name' => ['required', 'string', 'max:255'],
            'opening_hours' => ['missing'],
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
                    ->where(function (Builder $builder): void {
                        $builder->where('country_code', $this->input('country_code'));
                    }),
            ],
            'type' => ['required', Rule::enum(FacilityType::class)],
        ];
    }
}
```
