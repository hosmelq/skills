# Update Tests: Validation Contact Fields

Pest PATCH update: Whole contact-field dataset plus the no-displayable-value case in its named general error bag.

## Complete block

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\patch;

use App\Models\Member;
use Illuminate\Support\Str;

describe('update', function (): void {
    it('validates fields', function (array $data, array $expected): void {
        $member = Member::factory()->createOne();

        signIn(team: $member->team);

        $response = patch(route('teams.members.update', [
            'team' => $member->team,
            'member' => $member,
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
        'email:dns' => [
            'data' => [
                'email' => 'test@site.test',
            ],
            'expected' => [
                'email' => 'The email field must be a valid email address.',
            ],
        ],
        'email:strict' => [
            'data' => [
                'email' => 'test()@site.test',
            ],
            'expected' => [
                'email' => 'The email field must be a valid email address.',
            ],
        ],
        'indisposable' => [
            'data' => [
                'email' => 'test@0-mail.com',
            ],
            'expected' => [
                'email' => "This email address can't be used. Please try a different email.",
            ],
        ],
        'max:2000 (string)' => [
            'data' => [
                'note' => Str::repeat('a', 2001),
            ],
            'expected' => [
                'note' => 'The note field must not be greater than 2000 characters.',
            ],
        ],
        'max:255 (string)' => [
            'data' => [
                'email' => Str::repeat('a', 256),
                'first_name' => Str::repeat('a', 256),
                'last_name' => Str::repeat('a', 256),
                'phone_number' => Str::repeat('a', 256),
            ],
            'expected' => [
                'email' => 'The email field must not be greater than 255 characters.',
                'first_name' => 'The first name field must not be greater than 255 characters.',
                'last_name' => 'The last name field must not be greater than 255 characters.',
                'phone_number' => 'The phone number field must not be greater than 255 characters.',
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
    ]);

    it('requires an email, name, or phone number', function (): void {
        $member = Member::factory()->createOne();

        signIn(team: $member->team);

        $response = patch(route('teams.members.update', [
            'team' => $member->team,
            'member' => $member,
        ]), [
            'email' => '',
            'first_name' => '',
            'last_name' => '',
            'phone_number' => '',
        ]);

        $response->assertRedirectBackWithErrors([
            'summary' => 'Please provide an email, name, or phone number.',
        ], null, '_general');
    });
});
```
