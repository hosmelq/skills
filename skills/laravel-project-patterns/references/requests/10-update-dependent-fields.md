# Requests: Update Dependent Fields

Validate supplied fields, complete country from the bound record when needed, and clear stale province after a country change. The province query uses the effective country; paired coordinates require both keys when either is present.

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\CountryCode;
use App\Enums\FacilityType;
use App\Models\Facility;
use App\Models\World\State;
use Illuminate\Container\Attributes\RouteParameter;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Override;
use Propaganistas\LaravelPhone\Rules\Phone;
use Stringable;

class UpdateFacilityRequest extends FormRequest
{
    /**
     * @return array<string, list<string|Stringable|ValidationRule>>
     */
    public function rules(#[RouteParameter('facility')] Facility $facility): array
    {
        return [
            'address1' => ['nullable', 'string', 'max:255'],
            'address2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'country_code' => ['sometimes', 'required', Rule::enum(CountryCode::class)],
            'latitude' => [
                'nullable',
                'numeric',
                'between:-90,90',
                'present_with:longitude',
                'required_with:longitude',
            ],
            'longitude' => [
                'nullable',
                'numeric',
                'between:-180,180',
                'present_with:latitude',
                'required_with:latitude',
            ],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
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
                    ->where(function (Builder $builder) use ($facility): void {
                        $builder->where(
                            'country_code',
                            $this->input('country_code', $facility->country_code),
                        );
                    }),
            ],
            'type' => ['sometimes', 'required', Rule::enum(FacilityType::class)],
        ];
    }

    #[Override]
    protected function prepareForValidation(): void
    {
        $facility = $this->route('facility');

        assert($facility instanceof Facility);

        if ($this->filled('province_code') && $this->isNotFilled('country_code')) {
            $this->merge([
                'country_code' => $facility->country_code->value,
            ]);
        }

        if (
            $this->filled('country_code')
            && $this->input('country_code') !== $facility->country_code->value
            && $this->isNotFilled('province_code')
        ) {
            $this->merge([
                'province_code' => null,
            ]);
        }
    }
}
```
