# Requests: Dependent Fields

Validate an address whose province must belong to the submitted country and whose coordinates are sent together.

- Scope `exists` rules on lookup tables by the related input read with `$this->enum()`.
- Give fields readable names in `lang/en/validation.php`, alphabetically.
- Test validation through the endpoint in the controller's feature test, inside the `describe` block of the action.

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

final class StoreClientAddressRequest extends FormRequest
{
    /**
     * @return array<string, list<string|Stringable|ValidationRule>>
     */
    public function rules(): array
    {
        return [
            'address1' => ['required', 'string', 'max:255'],
            'address2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
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

```php
<?php

declare(strict_types=1);

return [

    'attributes' => [
        'address1' => 'address line 1',
        'address2' => 'address line 2',
        'country_code' => 'country',
        'move_after_id' => 'previous item',
        'province_code' => 'province or state',
    ],

    'phone' => 'The :attribute field must be a valid phone number.',

];
```

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\post;

use App\Models\Client;

describe('store', function (): void {
    it('validates fields', function (array $data, array $expected): void {
        $client = Client::factory()->createOne();

        login(clinic: $client->clinic);

        $response = post(route('clinics.clients.addresses.store', [
            'clinic' => $client->clinic,
            'client' => $client,
        ]), $data);

        $response->assertRedirectBackWithErrors($expected);
    })->with([
        'enum' => [
            'data' => [
                'country_code' => 'invalid',
            ],
            'expected' => [
                'country_code' => 'The selected country is invalid.',
            ],
        ],
        'exists' => [
            'data' => [
                'country_code' => 'NI',
                'province_code' => 'NY',
            ],
            'expected' => [
                'province_code' => 'The selected province or state is invalid.',
            ],
        ],
        'required_with:latitude' => [
            'data' => [
                'latitude' => 1,
            ],
            'expected' => [
                'longitude' => 'The longitude field is required when latitude is present.',
            ],
        ],
    ]);
});
```
