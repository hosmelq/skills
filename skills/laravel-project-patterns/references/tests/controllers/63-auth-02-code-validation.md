# Authentication Tests: Verification Code Validation

Pest POST JSON email login: complete email/code validation dataset and separate expired or already used verification code rejection. All failures return 422 with exact field errors.

Order: field dataset, expired code, used code. Keep valid, DNS-invalid, syntax-invalid and disposable email fixtures distinct.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\postJson;

use App\Models\OneTimePassword;
use Illuminate\Support\Str;

it('validates fields', function (array $data, array $expected): void {
    $response = postJson(route('api.auth.email.login'), $data);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors($expected);
})->with([
    'digits:6' => [
        'data' => [
            'email' => 'casey@example.com',
            'code' => '42424',
        ],
        'expected' => [
            'code' => 'The code field must be 6 digits.',
        ],
    ],
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
    'exists' => [
        'data' => [
            'code' => '424242',
        ],
        'expected' => [
            'code' => 'The selected code is invalid.',
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
    'max:255 (string)' => [
        'data' => [
            'email' => Str::repeat('a', 256),
        ],
        'expected' => [
            'email' => 'The email field must not be greater than 255 characters.',
        ],
    ],
    'required' => [
        'data' => [],
        'expected' => [
            'code' => 'The code field is required.',
            'email' => 'The email field is required.',
        ],
    ],
]);

it('rejects an expired verification code', function (): void {
    OneTimePassword::factory()->expired()->createOne([
        'code' => '424242',
        'email' => 'casey.sample@gmail.com',
    ]);

    $response = postJson(route('api.auth.email.login'), [
        'code' => '424242',
        'email' => 'casey.sample@gmail.com',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors([
            'code' => 'The selected code is invalid.',
        ]);
});

it('rejects an already used verification code', function (): void {
    OneTimePassword::factory()->used()->createOne([
        'code' => '424242',
        'email' => 'casey.sample@gmail.com',
    ]);

    $response = postJson(route('api.auth.email.login'), [
        'code' => '424242',
        'email' => 'casey.sample@gmail.com',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors([
            'code' => 'The selected code is invalid.',
        ]);
});
```
