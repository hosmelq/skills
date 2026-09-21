# Update Tests: Validation Settings Fields

Pest PATCH update: Whole settings dataset: booleans, enum, timezone, optional/required constraints, email and numeric identifier bounds.

The minimum-length row supplies a nonnumeric value; its error assertion does not isolate a clean numeric boundary.

## Validates fields

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\patch;

use App\Models\Team;
use Illuminate\Support\Str;

describe('update', function (): void {
    it('validates fields', function (array $data, array $expected): void {
        $team = Team::factory()->createOne();

        signIn(team: $team);

        $response = patch(route('teams.update', [
            'team' => $team,
        ]), $data);

        $response->assertRedirectBackWithErrors($expected);
    })->with([
        'boolean' => [
            'data' => [
                'cabinets_enabled' => 'invalid',
                'shipments_enabled' => 'invalid',
            ],
            'expected' => [
                'cabinets_enabled' => 'The cabinets enabled field must be true or false.',
                'shipments_enabled' => 'The shipments enabled field must be true or false.',
            ],
        ],
        'email' => [
            'data' => [
                'contact_email' => 'test@',
            ],
            'expected' => [
                'contact_email' => 'The contact email field must be a valid email address.',
            ],
        ],
        'email:dns' => [
            'data' => [
                'contact_email' => 'test@site.test',
            ],
            'expected' => [
                'contact_email' => 'The contact email field must be a valid email address.',
            ],
        ],
        'email:strict' => [
            'data' => [
                'contact_email' => 'test()@site.test',
            ],
            'expected' => [
                'contact_email' => 'The contact email field must be a valid email address.',
            ],
        ],
        'enum' => [
            'data' => [
                'country_code' => 'invalid',
                'cabinet_code_format_alphabet_type' => 'invalid',
                'cabinet_provisioning_mode' => 'invalid',
                'unit_system' => 'invalid',
                'weight_unit' => 'invalid',
            ],
            'expected' => [
                'country_code' => 'The selected country code is invalid.',
                'cabinet_code_format_alphabet_type' => 'The selected cabinet code format alphabet type is invalid.',
                'cabinet_provisioning_mode' => 'The selected cabinet provisioning mode is invalid.',
                'unit_system' => 'The selected unit system is invalid.',
                'weight_unit' => 'The selected weight unit is invalid.',
            ],
        ],
        'indisposable' => [
            'data' => [
                'contact_email' => 'test@0-mail.com',
            ],
            'expected' => [
                'contact_email' => "This email address can't be used. Please try a different email.",
            ],
        ],
        'max:20 (string)' => [
            'data' => [
                'cabinet_code_format_length' => 21,
                'cabinet_code_format_prefix' => Str::repeat('a', 21),
            ],
            'expected' => [
                'cabinet_code_format_length' => 'The cabinet code format length field must not be greater than 20.',
                'cabinet_code_format_prefix' => 'The cabinet code format prefix field must not be greater than 20 characters.',
            ],
        ],
        'max:255 (string)' => [
            'data' => [
                'contact_email' => Str::repeat('a', 256),
                'contact_phone_number' => Str::repeat('a', 256),
                'name' => Str::repeat('a', 256),
            ],
            'expected' => [
                'contact_email' => 'The contact email field must not be greater than 255 characters.',
                'contact_phone_number' => 'The contact phone number field must not be greater than 255 characters.',
                'name' => 'The name field must not be greater than 255 characters.',
            ],
        ],
        'min:4 (string)' => [
            'data' => [
                'cabinet_code_format_length' => 'a',
            ],
            'expected' => [
                'cabinet_code_format_length' => 'The cabinet code format length field must be at least 4.',
            ],
        ],
        'numeric' => [
            'data' => [
                'cabinet_code_format_length' => 'invalid',
            ],
            'expected' => [
                'cabinet_code_format_length' => 'The cabinet code format length field must be a number.',
            ],
        ],
        'phone' => [
            'data' => [
                'contact_phone_number' => '+503 8888 8888',
            ],
            'expected' => [
                'contact_phone_number' => 'The contact phone number field must be a valid number.',
            ],
        ],
        'sometimes (required)' => [
            'data' => [
                'country_code' => '',
                'cabinet_code_format_alphabet_type' => '',
                'cabinet_code_format_length' => '',
                'cabinet_provisioning_mode' => '',
                'cabinets_enabled' => '',
                'name' => '',
                'shipments_enabled' => '',
                'timezone' => '',
                'unit_system' => '',
                'weight_unit' => '',
            ],
            'expected' => [
                'country_code' => 'The country code field is required.',
                'cabinet_code_format_alphabet_type' => 'The cabinet code format alphabet type field is required.',
                'cabinet_code_format_length' => 'The cabinet code format length field is required.',
                'cabinet_provisioning_mode' => 'The cabinet provisioning mode field is required.',
                'cabinets_enabled' => 'The cabinets enabled field is required.',
                'name' => 'The name field is required.',
                'shipments_enabled' => 'The shipments enabled field is required.',
                'timezone' => 'The timezone field is required.',
                'unit_system' => 'The unit system field is required.',
                'weight_unit' => 'The weight unit field is required.',
            ],
        ],
        'timezone' => [
            'data' => [
                'timezone' => 'invalid',
            ],
            'expected' => [
                'timezone' => 'The timezone field must be a valid timezone.',
            ],
        ],
    ]);
});
```
