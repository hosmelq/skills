# Authentication Tests: Apple Field Validation

Pest POST JSON Apple login: complete named dataset for overlong profile names and required identity token/nonce, with exact 422 field errors.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\postJson;

use Illuminate\Support\Str;

it('validates fields', function (array $data, array $expected): void {
    $response = postJson(route('api.auth.apple.login'), $data);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors($expected);
})->with([
    'max:255 (string)' => [
        'data' => [
            'first_name' => Str::repeat('a', 256),
            'last_name' => Str::repeat('a', 256),
        ],
        'expected' => [
            'first_name' => 'The first name field must not be greater than 255 characters.',
            'last_name' => 'The last name field must not be greater than 255 characters.',
        ],
    ],
    'required' => [
        'data' => [],
        'expected' => [
            'id_token' => 'The id token field is required.',
            'nonce' => 'The nonce field is required.',
        ],
    ],
]);
```
