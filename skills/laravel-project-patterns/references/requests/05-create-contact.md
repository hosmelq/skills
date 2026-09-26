# Requests: Create Contact Fields

Normalize valid phone input to E.164 before scoped uniqueness. After ordinary validation succeeds, require one displayable email, name or phone; report the summary in the _general bag. Phone and indisposable rules require their installed packages.

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\CountryCode;
use App\Models\Member;
use App\Models\Team;
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

class StoreMemberRequest extends FormRequest
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
                    'summary' => __('Please provide an email, name, or phone number.'),
                ])->errorBag('_general');
            },
        ];
    }

    /**
     * @return array<string, list<string|Stringable|ValidationRule>>
     */
    public function rules(#[RouteParameter('team')] Team $team): array
    {
        return [
            'email' => [
                'nullable',
                'string',
                'max:255',
                'email:strict,dns',
                'indisposable',
                Rule::unique(Member::class)
                    ->where('team_id', $team->id)
                    ->withoutTrashed(),
            ],
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:2000'],
            'phone_number' => [
                'nullable',
                'string',
                'max:255',
                new Phone()->country(CountryCode::values()),
                Rule::unique(Member::class)
                    ->where('team_id', $team->id)
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
        return filled($this->input('email'))
            || filled($this->input('first_name'))
            || filled($this->input('last_name'))
            || filled($this->input('phone_number'));
    }
}
```
