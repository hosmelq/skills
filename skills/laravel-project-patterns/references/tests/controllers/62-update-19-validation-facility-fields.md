# Update Tests: Validation Facility Fields

Pest PATCH update: Whole geographic-record update dataset, retaining string/enum requirements and both coordinate bounds and numeric cases.

## Validates fields

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\patch;

use App\Models\Facility;
use Illuminate\Support\Str;

describe('update', function (): void {
    it('validates fields', function (array $data, array $expected): void {
        $facility = Facility::factory()->createOne();

        login(team: $facility->team);

        $response = patch(route('teams.facilities.update', [
            'team' => $facility->team,
            'facility' => $facility,
        ]), $data);

        $response->assertRedirectBackWithErrors($expected);
    })->with([
        'between:-180,180' => [
            'data' => [
                'longitude' => 181,
            ],
            'expected' => [
                'longitude' => 'The longitude field must be between -180 and 180.',
            ],
        ],
        'between:-180,180 (minimum)' => [
            'data' => [
                'longitude' => -181,
            ],
            'expected' => [
                'longitude' => 'The longitude field must be between -180 and 180.',
            ],
        ],
        'between:-90,90' => [
            'data' => [
                'latitude' => 91,
            ],
            'expected' => [
                'latitude' => 'The latitude field must be between -90 and 90.',
            ],
        ],
        'between:-90,90 (minimum)' => [
            'data' => [
                'latitude' => -91,
            ],
            'expected' => [
                'latitude' => 'The latitude field must be between -90 and 90.',
            ],
        ],
        'enum' => [
            'data' => [
                'country_code' => 'invalid',
                'type' => 'invalid',
            ],
            'expected' => [
                'country_code' => 'The selected country code is invalid.',
                'type' => 'The selected type is invalid.',
            ],
        ],
        'exists' => [
            'data' => [
                'country_code' => 'NI',
                'province_code' => 'XX',
            ],
            'expected' => [
                'province_code' => 'The selected province code is invalid.',
            ],
        ],
        'max:3 (string)' => [
            'data' => [
                'province_code' => Str::repeat('a', 4),
            ],
            'expected' => [
                'province_code' => 'The province code field must not be greater than 3 characters.',
            ],
        ],
        'max:255 (string)' => [
            'data' => [
                'address1' => Str::repeat('a', 256),
                'address2' => Str::repeat('b', 256),
                'city' => Str::repeat('c', 256),
                'name' => Str::repeat('d', 256),
                'phone_number' => Str::repeat('e', 256),
                'postal_code' => Str::repeat('e', 256),
            ],
            'expected' => [
                'address1' => 'The address1 field must not be greater than 255 characters.',
                'address2' => 'The address2 field must not be greater than 255 characters.',
                'city' => 'The city field must not be greater than 255 characters.',
                'name' => 'The name field must not be greater than 255 characters.',
                'phone_number' => 'The phone number field must not be greater than 255 characters.',
                'postal_code' => 'The postal code field must not be greater than 255 characters.',
            ],
        ],
        'missing' => [
            'data' => [
                'opening_hours' => [
                    'monday' => ['08:00-17:00'],
                ],
            ],
            'expected' => [
                'opening_hours' => 'The opening hours field must be missing.',
            ],
        ],
        'numeric' => [
            'data' => [
                'latitude' => 'north',
                'longitude' => 'west',
            ],
            'expected' => [
                'latitude' => 'The latitude field must be a number.',
                'longitude' => 'The longitude field must be a number.',
            ],
        ],
        'phone' => [
            'data' => [
                'phone_number' => '8888',
            ],
            'expected' => [
                'phone_number' => 'The phone number field must be a valid number.',
            ],
        ],
        'phone (country_code)' => [
            'data' => [
                'phone_number' => '+503 8888 8888',
            ],
            'expected' => [
                'phone_number' => 'The phone number field must be a valid number.',
            ],
        ],
        'present_with:latitude' => [
            'data' => [
                'latitude' => null,
            ],
            'expected' => [
                'longitude' => 'The longitude field must be present when latitude is present.',
            ],
        ],
        'present_with:longitude' => [
            'data' => [
                'longitude' => null,
            ],
            'expected' => [
                'latitude' => 'The latitude field must be present when longitude is present.',
            ],
        ],
        'required_with:latitude' => [
            'data' => [
                'latitude' => 1,
                'longitude' => null,
            ],
            'expected' => [
                'longitude' => 'The longitude field is required when latitude is present.',
            ],
        ],
        'required_with:longitude' => [
            'data' => [
                'latitude' => null,
                'longitude' => 1,
            ],
            'expected' => [
                'latitude' => 'The latitude field is required when longitude is present.',
            ],
        ],
        'sometimes (required)' => [
            'data' => [
                'country_code' => '',
                'name' => '',
                'type' => '',
            ],
            'expected' => [
                'country_code' => 'The country code field is required.',
                'name' => 'The name field is required.',
                'type' => 'The type field is required.',
            ],
        ],
    ]);
});
```
