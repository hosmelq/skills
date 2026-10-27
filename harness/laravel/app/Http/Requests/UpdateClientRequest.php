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

final class UpdateClientRequest extends FormRequest
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
    public function rules(
        #[RouteParameter('clinic')] Clinic $clinic,
        #[RouteParameter('client')] Client $client,
    ): array {
        return [
            'email' => [
                'nullable',
                'string',
                'max:255',
                'email:strict,dns',
                Rule::unique(Client::class)
                    ->ignore($client)
                    ->where('clinic_id', $clinic->id)
                    ->withoutTrashed(),
            ],
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'phone_number' => [
                'nullable',
                'string',
                'max:255',
                new Phone()->country(CountryCode::values()),
                Rule::unique(Client::class)
                    ->ignore($client)
                    ->where('clinic_id', $clinic->id)
                    ->withoutTrashed(),
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
        $client = $this->route('client');

        assert($client instanceof Client);

        return filled($this->input('email', $client->email))
            || filled($this->input('first_name', $client->first_name))
            || filled($this->input('last_name', $client->last_name))
            || filled($this->input('phone_number', $client->phone_number?->formatE164()));
    }
}
