# Requests: Store Request

Validate a create endpoint with a form request whose rules use the route's tenant, normalizing phone numbers before validation and rejecting submissions without any identifying field.

- Inject route models into `rules()` with `#[RouteParameter]`; scope `unique` rules to the tenant without trashed records.
- Normalize phone numbers to E.164 in `prepareForValidation()` so uniqueness compares stored values; invalid numbers are left for the `Phone` rule.
- Report a form-level error from `after()` in the `_general` bag, only when no field failed.
- Test validation through the endpoint in the controller's feature test, inside the `describe` block of the action.

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\CountryCode;
use App\Models\Client;
use App\Models\Clinic;
use Closure;
use Illuminate\Container\Attributes\RouteParameter;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;
use libphonenumber\NumberParseException;
use Override;
use Propaganistas\LaravelPhone\PhoneNumber;
use Propaganistas\LaravelPhone\Rules\Phone;
use Stringable;

final class StoreClientRequest extends FormRequest
{
    /**
     * @return array<int, Closure(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($this->hasDisplayableValue()) {
                    return;
                }

                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                throw ValidationException::withMessages([
                    'summary' => trans()->string('client.validation.displayable_value'),
                ])->errorBag('_general');
            },
        ];
    }

    /**
     * @return array<string, list<string|Stringable|ValidationRule>>
     */
    public function rules(#[RouteParameter('clinic')] Clinic $clinic): array
    {
        return [
            'email' => [
                'nullable',
                'string',
                'max:255',
                'email:strict,dns',
                Rule::unique(Client::class)->where('clinic_id', $clinic->id)->withoutTrashed(),
            ],
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'phone_number' => [
                'nullable',
                'string',
                'max:255',
                new Phone()->country(CountryCode::values()),
                Rule::unique(Client::class)->where('clinic_id', $clinic->id)->withoutTrashed(),
            ],
        ];
    }

    #[Override]
    protected function prepareForValidation(): void
    {
        if ($this->isNotFilled('phone_number')) {
            return;
        }

        $phoneNumber = new PhoneNumber(
            $this->string('phone_number')->toString(),
            CountryCode::values(),
        );

        if (! $phoneNumber->isValid()) {
            return;
        }

        try {
            $formattedPhoneNumber = $phoneNumber->formatE164();
        } catch (NumberParseException) {
            return;
        }

        $this->merge([
            'phone_number' => $formattedPhoneNumber,
        ]);
    }

    private function hasDisplayableValue(): bool
    {
        return filled($this->input('email'))
            || filled($this->input('first_name'))
            || filled($this->input('last_name'))
            || filled($this->input('phone_number'));
    }
}
```

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\post;

use App\Models\Client;
use App\Models\Clinic;

describe('store', function (): void {
    it('validates fields', function (array $data, array $expected): void {
        $clinic = Clinic::factory()->createOne();

        login(clinic: $clinic);

        $response = post(route('clinics.clients.store', [
            'clinic' => $clinic,
        ]), $data);

        $response->assertRedirectBackWithErrors($expected);
    })->with([
        'email' => [
            'data' => [
                'email' => 'test@',
            ],
            'expected' => [
                'email' => 'The email field must be a valid email address.',
            ],
        ],
        'max:255 (string)' => [
            'data' => [
                'first_name' => str_repeat('a', 256),
            ],
            'expected' => [
                'first_name' => 'The first name field must not be greater than 255 characters.',
            ],
        ],
        'phone' => [
            'data' => [
                'phone_number' => '8888',
            ],
            'expected' => [
                'phone_number' => 'The phone number field must be a valid phone number.',
            ],
        ],
    ]);

    it('requires an email, name, or phone number', function (): void {
        $clinic = Clinic::factory()->createOne();

        login(clinic: $clinic);

        $response = post(route('clinics.clients.store', [
            'clinic' => $clinic,
        ]), [
            'email' => '',
            'first_name' => '',
            'last_name' => '',
            'phone_number' => '',
        ]);

        $response->assertRedirectBackWithErrors([
            'summary' => 'Enter an email, name, or phone number.',
        ], null, '_general');
    });

    it('validates phone number uniqueness within the same clinic', function (): void {
        $client = Client::factory()->createOne([
            'phone_number' => '+50588888888',
        ]);

        login(clinic: $client->clinic);

        $response = post(route('clinics.clients.store', [
            'clinic' => $client->clinic,
        ]), [
            'phone_number' => '+505 8888 8888',
        ]);

        $response->assertRedirectBackWithErrors([
            'phone_number' => 'The phone number has already been taken.',
        ]);
    });
});
```
