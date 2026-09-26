# Requests: Update an Address

Fill the stored country when a province is supplied without a filled country. Clear province when country changes without a filled province. present_with plus required_with distinguishes omitted coordinates, paired nulls and an incomplete pair.

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\CountryCode;
use App\Models\MemberAddress;
use App\Models\World\State;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Override;
use Propaganistas\LaravelPhone\Rules\Phone;
use Stringable;

class UpdateMemberAddressRequest extends FormRequest
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
            'country_code' => ['sometimes', 'required', Rule::enum(CountryCode::class)],
            'first_name' => ['nullable', 'string', 'max:255'],
            'label' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
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

    #[Override]
    protected function prepareForValidation(): void
    {
        $address = $this->route('address');

        assert($address instanceof MemberAddress);

        if ($this->filled('province_code') && $this->isNotFilled('country_code')) {
            $this->merge([
                'country_code' => $address->country_code->value,
            ]);
        }

        if (
            $this->filled('country_code')
            && $this->input('country_code') !== $address->country_code->value
            && $this->isNotFilled('province_code')
        ) {
            $this->merge([
                'province_code' => null,
            ]);
        }
    }
}
```
