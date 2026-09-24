# Store Tests: Validation Address Fields

POST store: Complete postal/contact/geographic dataset: both coordinate extrema and paired fields, country/province and string/phone constraints.

The country-restricted phone row uses a valid number from outside the allowed `CountryCode` values; preserve that exclusion when adapting the fixture.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\post;

use App\Models\Member;
use Illuminate\Support\Str;

describe('store', function (): void {
    it('validates fields', function (array $data, array $expected): void {
        $member = Member::factory()->createOne();

        login(team: $member->team);

        $response = post(route('teams.members.addresses.store', [
            'team' => $member->team,
            'member' => $member,
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
            ],
            'expected' => [
                'country_code' => 'The selected country code is invalid.',
            ],
        ],
        'exists' => [
            'data' => [
                'country_code' => 'US',
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
                'address2' => Str::repeat('a', 256),
                'city' => Str::repeat('a', 256),
                'company' => Str::repeat('a', 256),
                'first_name' => Str::repeat('a', 256),
                'label' => Str::repeat('a', 256),
                'last_name' => Str::repeat('a', 256),
                'phone_number' => Str::repeat('a', 256),
                'postal_code' => Str::repeat('a', 256),
            ],
            'expected' => [
                'address1' => 'The address1 field must not be greater than 255 characters.',
                'address2' => 'The address2 field must not be greater than 255 characters.',
                'city' => 'The city field must not be greater than 255 characters.',
                'company' => 'The company field must not be greater than 255 characters.',
                'first_name' => 'The first name field must not be greater than 255 characters.',
                'label' => 'The label field must not be greater than 255 characters.',
                'last_name' => 'The last name field must not be greater than 255 characters.',
                'phone_number' => 'The phone number field must not be greater than 255 characters.',
                'postal_code' => 'The postal code field must not be greater than 255 characters.',
            ],
        ],
        'phone' => [
            'data' => [
                'country_code' => 'US',
                'phone_number' => '8888',
            ],
            'expected' => [
                'phone_number' => 'The phone number field must be a valid number.',
            ],
        ],
        'phone (country_code)' => [
            'data' => [
                'country_code' => 'US',
                'phone_number' => '+44 20 7946 0958',
            ],
            'expected' => [
                'phone_number' => 'The phone number field must be a valid number.',
            ],
        ],
        'required' => [
            'data' => [],
            'expected' => [
                'country_code' => 'The country code field is required.',
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
        'required_with:longitude' => [
            'data' => [
                'longitude' => 1,
            ],
            'expected' => [
                'latitude' => 'The latitude field is required when longitude is present.',
            ],
        ],
    ]);
});
```
